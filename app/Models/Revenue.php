<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Revenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'revenue_number',
        'franchise_id',
        'unit_id',
        'user_id',
        'type',
        'category',
        'amount',
        'currency',
        'description',
        'revenue_date',
        'period_year',
        'period_month',
        'period_start_date',
        'period_end_date',
        'source',
        'customer_name',
        'customer_email',
        'customer_phone',
        'invoice_number',
        'receipt_number',
        'payment_method',
        'payment_reference',
        'payment_status',
        'tax_amount',
        'discount_amount',
        'net_amount',
        'line_items',
        'metadata',
        'status',
        'verified_by',
        'verified_at',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_end_date',
        'parent_revenue_id',
        'attachments',
        'notes',
        'is_auto_generated',
        'recorded_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'net_amount' => 'float',
        'revenue_date' => 'date',
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'recurrence_end_date' => 'date',
        'verified_at' => 'datetime',
        'recorded_at' => 'datetime',
        'is_recurring' => 'boolean',
        'is_auto_generated' => 'boolean',
        'line_items' => 'array',
        'metadata' => 'array',
        'attachments' => 'array',
    ];

    // Boot method to generate revenue number and set period
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->revenue_number) {
                $model->revenue_number = static::generateRevenueNumber();
            }

            // Auto-set period year and month from revenue_date
            if ($model->revenue_date) {
                $date = Carbon::parse($model->revenue_date);
                $model->period_year = $date->year;
                $model->period_month = $date->month;
            }

            // Set recorded_at if not provided
            if (!$model->recorded_at) {
                $model->recorded_at = now();
            }

            // Calculate net amount if not provided
            if (!$model->net_amount) {
                $model->net_amount = $model->amount - $model->discount_amount + $model->tax_amount;
            }
        });
    }

    // Relationships
    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function parentRevenue(): BelongsTo
    {
        return $this->belongsTo(Revenue::class, 'parent_revenue_id');
    }

    public function childRevenues(): HasMany
    {
        return $this->hasMany(Revenue::class, 'parent_revenue_id');
    }

    // Accessors
    public function getIsVerifiedAttribute(): bool
    {
        return $this->status === 'verified';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsDisputedAttribute(): bool
    {
        return $this->status === 'disputed';
    }

    public function getFormattedAmountAttribute(): string
    {
        $currency = $this->currency ?? 'SAR';
        return number_format($this->amount, 2) . ' ' . $currency;
    }

    public function getFormattedNetAmountAttribute(): string
    {
        $currency = $this->currency ?? 'SAR';
        return number_format($this->net_amount, 2) . ' ' . $currency;
    }

    public function getDiscountPercentageAttribute(): float
    {
        if ($this->amount == 0) {
            return 0;
        }

        return ($this->discount_amount / $this->amount) * 100;
    }

    public function getTaxPercentageAttribute(): float
    {
        if ($this->amount == 0) {
            return 0;
        }

        return ($this->tax_amount / $this->amount) * 100;
    }

    // Methods
    public function isVerified(): bool
    {
        return $this->getIsVerifiedAttribute();
    }

    public function isPending(): bool
    {
        return $this->getIsPendingAttribute();
    }

    public function isDisputed(): bool
    {
        return $this->getIsDisputedAttribute();
    }

    public function verify(int $verifiedBy = null): void
    {
        $this->update([
            'status' => 'verified',
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
        ]);
    }

    public function dispute(string $reason = null): void
    {
        $this->update([
            'status' => 'disputed',
            'notes' => $this->notes . "\n\nDisputed: " . $reason,
        ]);
    }

    public function refund(string $reason = null): void
    {
        // Create a refund revenue record
        static::create([
            'revenue_number' => static::generateRevenueNumber(),
            'franchise_id' => $this->franchise_id,
            'unit_id' => $this->unit_id,
            'user_id' => $this->user_id,
            'type' => 'refund',
            'category' => $this->category,
            'amount' => -$this->amount, // Negative amount for refund
            'currency' => $this->currency,
            'description' => 'Refund for: ' . $this->description,
            'revenue_date' => now()->toDateString(),
            'tax_amount' => -$this->tax_amount,
            'discount_amount' => 0,
            'net_amount' => -$this->net_amount,
            'payment_status' => 'completed',
            'status' => 'verified',
            'notes' => $reason,
            'parent_revenue_id' => $this->getKey(),
        ]);

        $this->update(['payment_status' => 'refunded']);
    }

    public function addLineItem(array $item): void
    {
        $lineItems = $this->line_items ?? [];
        $lineItems[] = $item;
        $this->update(['line_items' => $lineItems]);
    }

    public function addAttachment(string $filePath): void
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = $filePath;
        $this->update(['attachments' => $attachments]);
    }

    public function createRecurringRevenue(): void
    {
        if (!$this->is_recurring || !$this->recurrence_type) {
            return;
        }

        $nextDate = $this->calculateNextRevenueDate();

        if ($this->recurrence_end_date && $nextDate->isAfter($this->recurrence_end_date)) {
            return;
        }

        static::create([
            'revenue_number' => static::generateRevenueNumber(),
            'franchise_id' => $this->franchise_id,
            'unit_id' => $this->unit_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'category' => $this->category,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'revenue_date' => $nextDate->toDateString(),
            'source' => $this->source,
            'payment_method' => $this->payment_method,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'net_amount' => $this->net_amount,
            'is_recurring' => $this->is_recurring,
            'recurrence_type' => $this->recurrence_type,
            'recurrence_interval' => $this->recurrence_interval,
            'recurrence_end_date' => $this->recurrence_end_date,
            'parent_revenue_id' => $this->parent_revenue_id ?? $this->getKey(),
            'is_auto_generated' => true,
        ]);
    }

    protected function calculateNextRevenueDate(): Carbon
    {
        $interval = $this->recurrence_interval ?? 1;
        $baseDate = Carbon::parse($this->revenue_date);

        return match ($this->recurrence_type) {
            'daily' => $baseDate->addDays($interval),
            'weekly' => $baseDate->addWeeks($interval),
            'monthly' => $baseDate->addMonths($interval),
            'quarterly' => $baseDate->addMonths($interval * 3),
            'yearly' => $baseDate->addYears($interval),
            default => $baseDate->addMonths($interval),
        };
    }

    protected static function generateRevenueNumber(): string
    {
        $prefix = 'REV';
        $year = date('Y');
        $month = date('m');
        
        // Get the last revenue number for this month
        $lastRevenue = static::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->orderBy('created_at', 'desc')
                            ->first();

        $sequence = 1;
        if ($lastRevenue) {
            $lastNumber = substr($lastRevenue->revenue_number, -4);
            $sequence = intval($lastNumber) + 1;
        }

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }

    // Static methods for reporting
    public static function getTotalRevenueByPeriod(int $franchiseId, int $year, int $month = null): float
    {
        $query = static::where('franchise_id', $franchiseId)
                      ->where('status', 'verified')
                      ->where('payment_status', 'completed')
                      ->where('period_year', $year);

        if ($month) {
            $query->where('period_month', $month);
        }

        return $query->sum('net_amount');
    }

    public static function getRevenueBreakdownByCategory(int $franchiseId, int $year, int $month = null): array
    {
        $query = static::where('franchise_id', $franchiseId)
                      ->where('status', 'verified')
                      ->where('payment_status', 'completed')
                      ->where('period_year', $year);

        if ($month) {
            $query->where('period_month', $month);
        }

        return $query->selectRaw('category, SUM(net_amount) as total')
                    ->groupBy('category')
                    ->pluck('total', 'category')
                    ->toArray();
    }

    // Query Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('revenue_date', [$startDate, $endDate]);
    }

    public function scopeByPeriod($query, int $year, int $month = null)
    {
        $query = $query->where('period_year', $year);
        
        if ($month) {
            $query = $query->where('period_month', $month);
        }

        return $query;
    }

    public function scopeByFranchise($query, int $franchiseId)
    {
        return $query->where('franchise_id', $franchiseId);
    }

    public function scopeByUnit($query, int $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeAutoGenerated($query)
    {
        return $query->where('is_auto_generated', true);
    }

    public function scopeSales($query)
    {
        return $query->where('type', 'sales');
    }

    public function scopeFees($query)
    {
        return $query->whereIn('type', ['franchise_fee', 'royalty', 'marketing_fee']);
    }
}
