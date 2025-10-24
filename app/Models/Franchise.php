<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Franchise extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchisor_id',
        'broker_id',
        'business_name',
        'brand_name',
        'industry',
        'description',
        'website',
        'logo',
        'business_registration_number',
        'tax_id',
        'business_type',
        'established_date',
        'headquarters_country',
        'headquarters_city',
        'headquarters_address',
        'contact_phone',
        'contact_email',
        'franchise_fee',
        'royalty_percentage',
        'marketing_fee_percentage',
        'total_units',
        'active_units',
        'status',
        'is_marketplace_listed',
        'plan',
        'business_hours',
        'social_media',
        'documents',
    ];

    protected $casts = [
        'established_date' => 'date',
        'franchise_fee' => 'float',
        'royalty_percentage' => 'float',
        'marketing_fee_percentage' => 'float',
        'is_marketplace_listed' => 'boolean',
        'business_hours' => 'array',
        'social_media' => 'array',
        'documents' => 'array',
    ];

    /**
     * Get the franchisor that owns this franchise
     */
    public function franchisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'franchisor_id');
    }

    /**
     * Get the broker assigned to this franchise
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    /**
     * Get all units belonging to this franchise
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get all leads for this franchise
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get all products for this franchise
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all documents for this franchise
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Check if franchise is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Update unit counts
     */
    public function updateUnitCounts(): void
    {
        $this->total_units = $this->units()->count();
        $this->active_units = $this->units()->where('status', 'active')->count();
        $this->save();
    }

    /**
     * Scope to filter active franchises
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by industry
     */
    public function scopeByIndustry($query, string $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Scope to filter marketplace listed franchises
     */
    public function scopeMarketplaceListed($query)
    {
        return $query->where('is_marketplace_listed', true);
    }
}
