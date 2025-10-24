<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchise_id',
        'franchisee_id',
        'unit_name',
        'unit_code',
        'unit_type',
        'nationality',
        'state_province',
        'city',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'phone',
        'email',
        'size_sqft',
        'monthly_rent',
        'initial_investment',
        'lease_start_date',
        'lease_end_date',
        'opening_date',
        'status',
        'employee_count',
        'monthly_revenue',
        'monthly_expenses',
        'operating_hours',
        'amenities',
        'equipment',
        'documents',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'size_sqft' => 'float',
        'monthly_rent' => 'float',
        'initial_investment' => 'float',
        'monthly_revenue' => 'float',
        'monthly_expenses' => 'float',
        'lease_start_date' => 'date',
        'lease_end_date' => 'date',
        'opening_date' => 'date',
        'operating_hours' => 'array',
        'amenities' => 'array',
        'equipment' => 'array',
        'documents' => 'array',
    ];

    // Relationships
    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function franchisee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'franchisee_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function technicalRequests(): HasMany
    {
        return $this->hasMany(TechnicalRequest::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'staff_unit')
            ->withPivot(['assigned_date', 'end_date', 'role', 'is_primary'])
            ->withTimestamps();
    }

    public function activeStaff(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->staff()->wherePivotNull('end_date');
    }

    /**
     * Products stocked in this unit.
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'unit_product_inventories')
            ->withPivot(['quantity', 'reorder_level'])
            ->using(Inventory::class)
            ->withTimestamps();
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state_province,
            $this->postal_code,
            $this->nationality,
        ]);

        return implode(', ', $parts);
    }

    public function getMonthlyProfitAttribute(): ?float
    {
        if ($this->monthly_revenue && $this->monthly_expenses) {
            return $this->monthly_revenue - $this->monthly_expenses;
        }

        return null;
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOperational(): bool
    {
        return in_array($this->status, ['active', 'training']);
    }

    public function updateFinancials(float $revenue, float $expenses): void
    {
        $this->update([
            'monthly_revenue' => $revenue,
            'monthly_expenses' => $expenses,
        ]);
    }

    public function assignFranchisee(int $franchiseeId): void
    {
        $this->update(['franchisee_id' => $franchiseeId]);
    }

    // Query Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('unit_type', $type);
    }

    public function scopeByCountry($query, string $country)
    {
        return $query->where('nationality', $country);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeProfitable($query)
    {
        return $query->whereRaw('monthly_revenue > monthly_expenses');
    }

    public function scopeNeedsAttention($query)
    {
        return $query->whereIn('status', ['temporarily_closed', 'construction'])
            ->orWhereRaw('monthly_revenue < monthly_expenses');
    }
}
