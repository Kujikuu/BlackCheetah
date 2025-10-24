<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'type',
        'category',
        'amount',
        'currency',
        'description',
        'franchise_id',
        'unit_id',
        'user_id',
        'transaction_date',
        'status',
        'payment_method',
        'reference_number',
        'vendor_customer',
        'metadata',
        'attachments',
        'notes',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_end_date',
        'parent_transaction_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'transaction_date' => 'date',
        'recurrence_end_date' => 'date',
        'is_recurring' => 'boolean',
        'metadata' => 'array',
        'attachments' => 'array',
    ];

    // Boot method to generate transaction number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->transaction_number) {
                $model->transaction_number = static::generateTransactionNumber();
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

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    public function childTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    // Accessors
    public function getIsRevenueAttribute(): bool
    {
        return in_array($this->type, ['revenue', 'franchise_fee']);
    }

    public function getIsExpenseAttribute(): bool
    {
        return in_array($this->type, ['expense', 'royalty', 'marketing_fee']);
    }

    public function getFormattedAmountAttribute(): string
    {
        $currency = $this->currency ?? 'SAR';
        return number_format($this->amount, 2) . ' ' . $currency;
    }

    // Methods
    public function isRevenue(): bool
    {
        return $this->getIsRevenueAttribute();
    }

    public function isExpense(): bool
    {
        return $this->getIsExpenseAttribute();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function refund(string $reason = null): void
    {
        // Create a refund transaction
        static::create([
            'transaction_number' => static::generateTransactionNumber(),
            'type' => 'refund',
            'category' => $this->category,
            'amount' => -$this->amount, // Negative amount for refund
            'currency' => $this->currency,
            'description' => 'Refund for: ' . $this->description,
            'franchise_id' => $this->franchise_id,
            'unit_id' => $this->unit_id,
            'user_id' => auth()->id(),
            'transaction_date' => now()->toDateString(),
            'status' => 'completed',
            'reference_number' => $this->transaction_number,
            'notes' => $reason,
            'parent_transaction_id' => $this->getKey(),
        ]);

        $this->update(['status' => 'refunded']);
    }

    public function addAttachment(string $filePath): void
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = $filePath;
        $this->update(['attachments' => $attachments]);
    }

    public function createRecurringTransaction(): void
    {
        if (!$this->is_recurring || !$this->recurrence_type) {
            return;
        }

        $nextDate = $this->calculateNextTransactionDate();

        if ($this->recurrence_end_date && $nextDate->isAfter($this->recurrence_end_date)) {
            return;
        }

        static::create([
            'transaction_number' => static::generateTransactionNumber(),
            'type' => $this->type,
            'category' => $this->category,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'franchise_id' => $this->franchise_id,
            'unit_id' => $this->unit_id,
            'user_id' => $this->user_id,
            'transaction_date' => $nextDate->toDateString(),
            'status' => 'pending',
            'payment_method' => $this->payment_method,
            'vendor_customer' => $this->vendor_customer,
            'notes' => $this->notes,
            'is_recurring' => $this->is_recurring,
            'recurrence_type' => $this->recurrence_type,
            'recurrence_interval' => $this->recurrence_interval,
            'recurrence_end_date' => $this->recurrence_end_date,
            'parent_transaction_id' => $this->parent_transaction_id ?? $this->getKey(),
        ]);
    }

    protected function calculateNextTransactionDate(): Carbon
    {
        $interval = $this->recurrence_interval ?? 1;
        $baseDate = Carbon::parse($this->transaction_date);

        return match ($this->recurrence_type) {
            'daily' => $baseDate->addDays($interval),
            'weekly' => $baseDate->addWeeks($interval),
            'monthly' => $baseDate->addMonths($interval),
            'quarterly' => $baseDate->addMonths($interval * 3),
            'yearly' => $baseDate->addYears($interval),
            default => $baseDate->addMonths($interval),
        };
    }

    protected static function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $year = date('Y');
        $month = date('m');
        
        // Get the last transaction number for this month
        $lastTransaction = static::whereYear('created_at', $year)
                                ->whereMonth('created_at', $month)
                                ->orderBy('id', 'desc')
                                ->first();

        $sequence = 1;
        if ($lastTransaction) {
            $lastNumber = substr($lastTransaction->transaction_number, -4);
            $sequence = intval($lastNumber) + 1;
        }

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
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

    public function scopeRevenue($query)
    {
        return $query->whereIn('type', ['revenue', 'franchise_fee']);
    }

    public function scopeExpenses($query)
    {
        return $query->whereIn('type', ['expense', 'royalty', 'marketing_fee']);
    }

    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeByMonth($query, int $year, int $month)
    {
        return $query->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $month);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->whereYear('transaction_date', $year);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
