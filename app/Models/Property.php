<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'broker_id',
        'title',
        'description',
        'property_type',
        'size_sqm',
        'state_province',
        'city',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'monthly_rent',
        'deposit_amount',
        'lease_term_months',
        'available_from',
        'amenities',
        'images',
        'status',
        'contact_info',
    ];

    protected $casts = [
        'size_sqm' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'monthly_rent' => 'float',
        'deposit_amount' => 'float',
        'lease_term_months' => 'integer',
        'available_from' => 'date',
        'amenities' => 'array',
        'images' => 'array',
    ];

    /**
     * Get the broker that owns this property
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    /**
     * Scope to filter available properties
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to filter by property type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('property_type', $type);
    }

    /**
     * Scope to filter by location
     */
    public function scopeByLocation($query, ?string $stateProvince = null, ?string $city = null)
    {
        if ($stateProvince) {
            $query->where('state_province', $stateProvince);
        }

        if ($city) {
            $query->where('city', $city);
        }

        return $query;
    }

    /**
     * Check if property is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state_province,
            $this->postal_code,
            'Saudi Arabia',
        ]);

        return implode(', ', $parts);
    }
}
