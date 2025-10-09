<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TechnicalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'requester_id',
        'assigned_to',
        'franchise_id',
        'unit_id',
        'affected_system',
        'steps_to_reproduce',
        'expected_behavior',
        'actual_behavior',
        'browser_version',
        'operating_system',
        'device_type',
        'attachments',
        'internal_notes',
        'resolution_notes',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'response_time_hours',
        'resolution_time_hours',
        'satisfaction_rating',
        'satisfaction_feedback',
        'is_escalated',
        'escalated_at',
    ];

    protected $casts = [
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'escalated_at' => 'datetime',
        'is_escalated' => 'boolean',
        'attachments' => 'array',
    ];

    // Boot method to generate ticket number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->ticket_number) {
                $model->ticket_number = static::generateTicketNumber();
            }
        });
    }

    // Relationships
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // Accessors
    public function getIsOpenAttribute(): bool
    {
        return in_array($this->status, ['open', 'in_progress', 'pending_info']);
    }

    public function getIsResolvedAttribute(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function getAgeInHoursAttribute(): int
    {
        return $this->created_at->diffInHours(now());
    }

    public function getResponseTimeStatusAttribute(): string
    {
        if ($this->first_response_at) {
            return 'responded';
        }

        $hours = $this->getAgeInHoursAttribute();
        $slaHours = $this->getSlaResponseHours();

        if ($hours > $slaHours) {
            return 'overdue';
        } elseif ($hours > ($slaHours * 0.8)) {
            return 'warning';
        }

        return 'on_time';
    }

    // Methods
    public function isOpen(): bool
    {
        return $this->getIsOpenAttribute();
    }

    public function isResolved(): bool
    {
        return $this->getIsResolvedAttribute();
    }

    public function respond(): void
    {
        if (!$this->first_response_at) {
            $responseTime = $this->created_at->diffInHours(now());
            
            $this->update([
                'first_response_at' => now(),
                'response_time_hours' => $responseTime,
                'status' => 'in_progress',
            ]);
        }
    }

    public function resolve(string $resolutionNotes = null): void
    {
        $resolutionTime = $this->created_at->diffInHours(now());

        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_time_hours' => $resolutionTime,
            'resolution_notes' => $resolutionNotes,
        ]);
    }

    public function close(int $rating = null, string $feedback = null): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'satisfaction_rating' => $rating,
            'satisfaction_feedback' => $feedback,
        ]);
    }

    public function escalate(): void
    {
        $this->update([
            'is_escalated' => true,
            'escalated_at' => now(),
            'priority' => $this->priority === 'urgent' ? 'urgent' : 'high',
        ]);
    }

    public function addAttachment(string $filePath): void
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = $filePath;
        $this->update(['attachments' => $attachments]);
    }

    public function assignTo(int $userId): void
    {
        $this->update(['assigned_to' => $userId]);
        
        // Auto-respond if not already responded
        if (!$this->first_response_at) {
            $this->respond();
        }
    }

    protected function getSlaResponseHours(): int
    {
        return match ($this->priority) {
            'urgent' => 1,
            'high' => 4,
            'medium' => 24,
            'low' => 72,
            default => 24,
        };
    }

    protected static function generateTicketNumber(): string
    {
        $prefix = 'TR';
        $year = date('Y');
        $month = date('m');
        
        // Get the last ticket number for this month
        $lastTicket = static::whereYear('created_at', $year)
                           ->whereMonth('created_at', $month)
                           ->orderBy('id', 'desc')
                           ->first();

        $sequence = 1;
        if ($lastTicket) {
            $lastNumber = substr($lastTicket->ticket_number, -4);
            $sequence = intval($lastNumber) + 1;
        }

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }

    // Query Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'pending_info']);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNull('first_response_at')
                    ->where('created_at', '<', now()->subHours(24));
    }

    public function scopeEscalated($query)
    {
        return $query->where('is_escalated', true);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }
}
