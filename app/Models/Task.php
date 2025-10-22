<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'priority',
        'status',
        'assigned_to',
        'created_by',
        'franchise_id',
        'unit_id',
        'lead_id',
        'due_date',
        'started_at',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'checklist',
        'attachments',
        'notes',
        'completion_notes',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_end_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'recurrence_end_date' => 'date',
        'is_recurring' => 'boolean',
        'checklist' => 'array',
        'attachments' => 'array',
    ];

    // Relationships
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               ! in_array($this->status, ['completed', 'cancelled']);
    }

    public function getCategoryAttribute(): string
    {
        // Map database type back to frontend category
        $typeToCategoryMap = [
            'operations' => 'Operations',
            'training' => 'Training',
            'maintenance' => 'Maintenance',
            'marketing' => 'Marketing',
            'finance' => 'Finance',
            'other' => 'HR', // Map 'other' back to HR for frontend
            'compliance' => 'Quality Control',
            'support' => 'Customer Service',
            'onboarding' => 'Training',
        ];

        return $typeToCategoryMap[$this->type] ?? 'Other';
    }

    public function getCategoryForBroker(): string
    {
        // Map database type to broker-specific categories
        $typeToCategoryMap = [
            'lead_management' => 'Lead Management',
            'sales' => 'Sales',
            'market_research' => 'Market Research',
            'onboarding' => 'Onboarding',
            'operations' => 'Operations',
            'training' => 'Training',
            'marketing' => 'Marketing',
            'other' => 'Other',
        ];

        return $typeToCategoryMap[$this->type] ?? 'Other';
    }

    public function getAssignedToUserAttribute()
    {
        return $this->assignedTo;
    }

    public function getProgressPercentageAttribute(): int
    {
        if (! $this->checklist || empty($this->checklist)) {
            return $this->status === 'completed' ? 100 : 0;
        }

        $completed = collect($this->checklist)->where('completed', true)->count();
        $total = count($this->checklist);

        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInHours($this->completed_at);
        }

        return null;
    }

    // Methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
    {
        return $this->getIsOverdueAttribute();
    }

    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete(?string $completionNotes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $completionNotes,
        ]);

        // Create next recurring task if applicable
        if ($this->is_recurring) {
            $this->createNextRecurringTask();
        }
    }

    public function updateChecklist(array $checklist): void
    {
        $this->update(['checklist' => $checklist]);
    }

    public function addAttachment(string $filePath): void
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = $filePath;
        $this->update(['attachments' => $attachments]);
    }

    protected function createNextRecurringTask(): void
    {
        if (! $this->is_recurring || ! $this->recurrence_type) {
            return;
        }

        $nextDueDate = $this->calculateNextDueDate();

        if ($this->recurrence_end_date && $nextDueDate->isAfter($this->recurrence_end_date)) {
            return;
        }

        static::create([
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'priority' => $this->priority,
            'assigned_to' => $this->assigned_to,
            'created_by' => $this->created_by,
            'franchise_id' => $this->franchise_id,
            'unit_id' => $this->unit_id,
            'lead_id' => $this->lead_id,
            'due_date' => $nextDueDate,
            'estimated_hours' => $this->estimated_hours,
            'checklist' => $this->checklist,
            'notes' => $this->notes,
            'is_recurring' => $this->is_recurring,
            'recurrence_type' => $this->recurrence_type,
            'recurrence_interval' => $this->recurrence_interval,
            'recurrence_end_date' => $this->recurrence_end_date,
        ]);
    }

    protected function calculateNextDueDate(): Carbon
    {
        $interval = $this->recurrence_interval ?? 1;
        $baseDate = $this->due_date ?? now();

        return match ($this->recurrence_type) {
            'daily' => $baseDate->addDays($interval),
            'weekly' => $baseDate->addWeeks($interval),
            'monthly' => $baseDate->addMonths($interval),
            'quarterly' => $baseDate->addMonths($interval * 3),
            'yearly' => $baseDate->addYears($interval),
            default => $baseDate->addDays($interval),
        };
    }

    // Query Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->whereBetween('due_date', [now(), now()->addDays($days)])
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }
}
