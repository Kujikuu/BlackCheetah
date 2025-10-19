<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'category',
        'attachments',
        'priority',
        'status',
        'requester_id',
    ];

    protected $casts = [
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

    // Methods
    public function isOpen(): bool
    {
        return $this->getIsOpenAttribute();
    }

    public function isResolved(): bool
    {
        return $this->getIsResolvedAttribute();
    }

    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

    public function addAttachment(string $filePath): void
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = $filePath;
        $this->update(['attachments' => $attachments]);
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

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }
}
