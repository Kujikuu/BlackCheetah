<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchise_id',
        'assigned_to',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'job_title',
        'country',
        'city',
        'address',
        'lead_source',
        'status',
        'priority',
        'estimated_investment',
        'franchise_fee_quoted',
        'notes',
        'expected_decision_date',
        'last_contact_date',
        'next_follow_up_date',
        'contact_attempts',
        'interests',
        'documents',
        'communication_log',
    ];

    protected $casts = [
        'estimated_investment' => 'decimal:2',
        'franchise_fee_quoted' => 'decimal:2',
        'expected_decision_date' => 'date',
        'last_contact_date' => 'date',
        'next_follow_up_date' => 'date',
        'interests' => 'array',
        'documents' => 'array',
        'communication_log' => 'array',
    ];

    /**
     * Get the franchise this lead belongs to
     */
    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    /**
     * Get the user assigned to this lead
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the notes for this lead
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the full name of the lead
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Check if lead is qualified
     */
    public function isQualified(): bool
    {
        return in_array($this->status, ['qualified', 'proposal_sent', 'negotiating']);
    }

    /**
     * Check if lead is closed
     */
    public function isClosed(): bool
    {
        return in_array($this->status, ['closed_won', 'closed_lost']);
    }

    /**
     * Check if lead is won
     */
    public function isWon(): bool
    {
        return $this->status === 'closed_won';
    }

    /**
     * Add communication log entry
     */
    public function addCommunicationLog(string $type, string $message, ?string $userId = null): void
    {
        $log = $this->communication_log ?? [];
        $log[] = [
            'type' => $type,
            'message' => $message,
            'user_id' => $userId,
            'timestamp' => now()->toISOString(),
        ];
        $this->communication_log = $log;
        $this->save();
    }

    /**
     * Increment contact attempts
     */
    public function incrementContactAttempts(): void
    {
        $this->increment('contact_attempts');
        $this->last_contact_date = now();
        $this->save();
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter qualified leads
     */
    public function scopeQualified($query)
    {
        return $query->whereIn('status', ['qualified', 'proposal_sent', 'negotiating']);
    }

    /**
     * Scope to filter leads needing follow-up
     */
    public function scopeNeedsFollowUp($query)
    {
        return $query->where('next_follow_up_date', '<=', now())
            ->whereNotIn('status', ['closed_won', 'closed_lost']);
    }
}
