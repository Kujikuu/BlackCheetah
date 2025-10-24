<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'inquiry_type',
        'franchise_id',
        'property_id',
        'message',
        'investment_budget',
        'preferred_location',
        'timeline',
        'status',
    ];

    protected $casts = [
        'inquiry_type' => 'string',
        'status' => 'string',
    ];

    /**
     * Get the franchise that this inquiry is about
     */
    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    /**
     * Get the property that this inquiry is about
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope to filter by inquiry type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('inquiry_type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if inquiry is new
     */
    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    /**
     * Mark as contacted
     */
    public function markAsContacted(): void
    {
        $this->update(['status' => 'contacted']);
    }

    /**
     * Mark as closed
     */
    public function markAsClosed(): void
    {
        $this->update(['status' => 'closed']);
    }
}
