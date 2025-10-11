<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchise_id',
        'unit_id',
        'period_type',
        'period_date',
        'revenue',
        'expenses',
        'royalties',
        'profit',
        'total_transactions',
        'average_transaction_value',
        'customer_reviews_count',
        'customer_rating',
        'employee_count',
        'customer_satisfaction_score',
        'growth_rate',
        'additional_metrics',
    ];

    protected $casts = [
        'period_date' => 'date',
        'revenue' => 'decimal:2',
        'expenses' => 'decimal:2',
        'royalties' => 'decimal:2',
        'profit' => 'decimal:2',
        'average_transaction_value' => 'decimal:2',
        'customer_rating' => 'decimal:2',
        'customer_satisfaction_score' => 'decimal:2',
        'growth_rate' => 'decimal:2',
        'additional_metrics' => 'array',
    ];

    // Relationships
    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // Accessors
    public function getProfitMarginAttribute(): float
    {
        return $this->revenue > 0 ? ($this->profit / $this->revenue) * 100 : 0;
    }

    public function getRoyaltyRateAttribute(): float
    {
        return $this->revenue > 0 ? ($this->royalties / $this->revenue) * 100 : 0;
    }

    public function getExpenseRatioAttribute(): float
    {
        return $this->revenue > 0 ? ($this->expenses / $this->revenue) * 100 : 0;
    }

    // Scopes
    public function scopeForPeriod($query, string $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    public function scopeForFranchise($query, int $franchiseId)
    {
        return $query->where('franchise_id', $franchiseId);
    }

    public function scopeForUnit($query, ?int $unitId)
    {
        if ($unitId === null) {
            return $query->whereNull('unit_id'); // Aggregated data
        }

        return $query->where('unit_id', $unitId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_date', [$startDate, $endDate]);
    }

    public function scopeOrderByDate($query, $direction = 'asc')
    {
        return $query->orderBy('period_date', $direction);
    }

    public function scopeLatest($query, int $limit = 10)
    {
        return $query->orderBy('period_date', 'desc')->limit($limit);
    }

    public function scopeByRating($query, $direction = 'desc')
    {
        return $query->orderBy('customer_rating', $direction);
    }

    public function scopeByRevenue($query, $direction = 'desc')
    {
        return $query->orderBy('revenue', $direction);
    }
}
