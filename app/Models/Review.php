<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'franchisee_id', // Who added this review
        'customer_name', // Customer being reviewed
        'customer_email',
        'customer_phone',
        'rating',
        'comment',
        'sentiment',
        'status',
        'internal_notes', // Private notes for franchisee
        'review_source', // How the review was collected
        'verified_purchase', // Franchisee confirms this was real customer
        'review_date', // When the actual customer interaction happened
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'verified_purchase' => 'boolean',
            'review_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function franchisee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'franchisee_id');
    }

    public function getFormattedReviewDateAttribute(): string
    {
        return $this->review_date->format('Y-m-d');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('review_source', $source);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeBySentiment($query, $sentiment)
    {
        return $query->where('sentiment', $sentiment);
    }
}
