<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Royalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'royalty_number',
        'franchise_id',
        'unit_id',
        'franchisee_id',
        'type',
        'period_year',
        'period_month',
        'period_start_date',
        'period_end_date',
        'gross_revenue',
        'royalty_percentage',
        'royalty_amount',
        'marketing_fee_percentage',
        'marketing_fee_amount',
        'technology_fee_amount',
        'total_amount',
        'adjustments',
        'adjustment_notes',
        'status',
        'due_date',
        'paid_date',
        'payment_method',
        'payment_reference',
        'late_fee',
        'revenue_breakdown',
        'attachments',
        'notes',
        'is_auto_generated',
        'generated_by',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'gross_revenue' => 'decimal:2',
        'royalty_percentage' => 'decimal:2',
        'royalty_amount' => 'decimal:2',
        'marketing_fee_percentage' => 'decimal:2',
        'marketing_fee_amount' => 'decimal:2',
        'technology_fee_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'late_fee' => 'decimal:2',
        'revenue_breakdown' => 'array',
        'attachments' => 'array',
        'is_auto_generated' => 'boolean',
    ];

    // Boot method to generate royalty number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->royalty_number) {
                $model->royalty_number = static::generateRoyaltyNumber();
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

    public function franchisee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'franchisee_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Accessors
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date && Carbon::parse($this->due_date)->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->getIsOverdueAttribute()) {
            return 0;
        }

        return Carbon::parse($this->due_date)->diffInDays(now());
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return '$'.number_format($this->total_amount, 2);
    }

    public function getPeriodDescriptionAttribute(): string
    {
        if ($this->period_month && $this->period_year) {
            return Carbon::createFromDate($this->period_year, $this->period_month, 1)->format('F Y');
        }

        if ($this->period_start_date && $this->period_end_date) {
            return $this->period_start_date->format('M j, Y').' - '.$this->period_end_date->format('M j, Y');
        }

        return 'N/A';
    }

    // Methods
    public function isPaid(): bool
    {
        return $this->getIsPaidAttribute();
    }

    public function isOverdue(): bool
    {
        return $this->getIsOverdueAttribute();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsPaid(?string $paymentMethod = null, ?string $paymentReference = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => now()->toDateString(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);

        // TODO: Create a transaction record if needed
        // Transaction::create([...]);
    }

    public function calculateLateFee(): void
    {
        if (! $this->isOverdue() || $this->late_fee > 0) {
            return;
        }

        $daysOverdue = $this->getDaysOverdueAttribute();
        $lateFeeRate = 0.05; // 5% late fee
        $lateFee = $this->total_amount * $lateFeeRate;

        $this->update([
            'late_fee' => $lateFee,
            'total_amount' => $this->total_amount + $lateFee,
        ]);
    }

    public function addAdjustment(float $amount, string $notes): void
    {
        $currentAdjustments = $this->adjustments ?? 0;
        $newTotal = $this->total_amount - $currentAdjustments + $amount;

        $this->update([
            'adjustments' => $amount,
            'adjustment_notes' => $notes,
            'total_amount' => $newTotal,
        ]);
    }

    public function addAttachment(string $filePath): void
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = $filePath;
        $this->update(['attachments' => $attachments]);
    }

    public function calculateRoyaltyAmounts(): void
    {
        $royaltyAmount = $this->gross_revenue * ($this->royalty_percentage / 100);
        $marketingFeeAmount = $this->gross_revenue * ($this->marketing_fee_percentage / 100);
        $totalAmount = $royaltyAmount + $marketingFeeAmount + ($this->technology_fee_amount ?? 0);

        $this->update([
            'royalty_amount' => $royaltyAmount,
            'marketing_fee_amount' => $marketingFeeAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    protected static function generateRoyaltyNumber(): string
    {
        $prefix = 'ROY';
        $year = date('Y');
        $month = date('m');

        // Get the last royalty number for this month
        $lastRoyalty = static::where('royalty_number', 'like', $prefix.$year.$month.'%')
            ->orderBy('royalty_number', 'desc')
            ->lockForUpdate()
            ->first();

        $sequence = 1;
        if ($lastRoyalty && $lastRoyalty->royalty_number) {
            $lastNumber = substr($lastRoyalty->royalty_number, -4);
            $sequence = intval($lastNumber) + 1;
        }

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }

    // Static methods for auto-generation
    public static function generateMonthlyRoyalties(int $year, int $month): void
    {
        $franchises = Franchise::active()->get();

        foreach ($franchises as $franchise) {
            foreach ($franchise->units as $unit) {
                if (! $unit->isActive()) {
                    continue;
                }

                // Check if royalty already exists for this period
                $existingRoyalty = static::where('franchise_id', $franchise->id)
                    ->where('unit_id', $unit->id)
                    ->where('period_year', $year)
                    ->where('period_month', $month)
                    ->where('type', 'monthly')
                    ->first();

                if ($existingRoyalty) {
                    continue;
                }

                // Calculate period dates
                $periodStart = Carbon::createFromDate($year, $month, 1);
                $periodEnd = $periodStart->copy()->endOfMonth();

                // Get revenue for the period (this would need to be implemented based on your revenue tracking)
                $grossRevenue = $unit->getMonthlyRevenue($year, $month);

                if ($grossRevenue > 0) {
                    $royalty = static::create([
                        'franchise_id' => $franchise->id,
                        'unit_id' => $unit->id,
                        'franchisee_id' => $unit->franchisee_id,
                        'type' => 'monthly',
                        'period_year' => $year,
                        'period_month' => $month,
                        'period_start_date' => $periodStart->toDateString(),
                        'period_end_date' => $periodEnd->toDateString(),
                        'gross_revenue' => $grossRevenue,
                        'royalty_percentage' => $franchise->royalty_percentage,
                        'marketing_fee_percentage' => $franchise->marketing_fee_percentage,
                        'technology_fee_amount' => 50.00, // Fixed technology fee
                        'status' => 'pending',
                        'due_date' => $periodEnd->addDays(15)->toDateString(), // Due 15 days after period end
                        'is_auto_generated' => true,
                        'generated_by' => null, // Will be set by the calling context
                    ]);

                    $royalty->calculateRoyaltyAmounts();
                }
            }
        }
    }

    // Query Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<', now());
    }

    public function scopeByPeriod($query, int $year, ?int $month = null)
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

    public function scopeAutoGenerated($query)
    {
        return $query->where('is_auto_generated', true);
    }
}
