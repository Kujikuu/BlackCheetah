<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Franchise;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['franchise', 'unit', 'user', 'parentTransaction']);

        // Apply filters
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('is_recurring')) {
            $query->recurring();
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        if ($request->has('month') && $request->has('year')) {
            $query->byMonth($request->month, $request->year);
        } elseif ($request->has('year')) {
            $query->byYear($request->year);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('transaction_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'message' => 'Transactions retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:revenue,expense,transfer,refund,adjustment',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'required|string|max:500',
            'transaction_date' => 'required|date',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'user_id' => 'required|exists:users,id',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric',
            'status' => 'required|in:pending,completed,cancelled,refunded',
            'notes' => 'nullable|string',
            'metadata' => 'nullable|array',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_end_date' => 'nullable|date|after:transaction_date',
            'parent_transaction_id' => 'nullable|exists:transactions,id',
            'attachments' => 'nullable|array'
        ]);

        $transaction = Transaction::create($validated);

        return response()->json([
            'success' => true,
            'data' => $transaction->load(['franchise', 'unit', 'user']),
            'message' => 'Transaction created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['franchise', 'unit', 'user', 'parentTransaction', 'childTransactions']);

        return response()->json([
            'success' => true,
            'data' => $transaction,
            'message' => 'Transaction retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:revenue,expense,transfer,refund,adjustment',
            'category' => 'sometimes|string|max:100',
            'amount' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'description' => 'sometimes|string|max:500',
            'transaction_date' => 'sometimes|date',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'user_id' => 'sometimes|exists:users,id',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric',
            'status' => 'sometimes|in:pending,completed,cancelled,refunded',
            'notes' => 'nullable|string',
            'metadata' => 'nullable|array',
            'is_recurring' => 'sometimes|boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_end_date' => 'nullable|date|after:transaction_date',
            'attachments' => 'nullable|array'
        ]);

        $transaction->update($validated);

        return response()->json([
            'success' => true,
            'data' => $transaction->load(['franchise', 'unit', 'user']),
            'message' => 'Transaction updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        // Check if transaction can be deleted
        if ($transaction->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete completed transaction'
            ], 422);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully'
        ]);
    }

    /**
     * Mark transaction as completed
     */
    public function complete(Transaction $transaction): JsonResponse
    {
        $transaction->markAsCompleted();

        return response()->json([
            'success' => true,
            'data' => $transaction,
            'message' => 'Transaction marked as completed'
        ]);
    }

    /**
     * Cancel transaction
     */
    public function cancel(Transaction $transaction): JsonResponse
    {
        $transaction->markAsCancelled();

        return response()->json([
            'success' => true,
            'data' => $transaction,
            'message' => 'Transaction cancelled successfully'
        ]);
    }

    /**
     * Refund transaction
     */
    public function refund(Request $request, Transaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'refund_amount' => 'nullable|numeric|min:0|max:' . $transaction->amount,
            'refund_reason' => 'required|string|max:500'
        ]);

        $refundTransaction = $transaction->refund(
            $validated['refund_amount'] ?? null,
            $validated['refund_reason']
        );

        return response()->json([
            'success' => true,
            'data' => $refundTransaction,
            'message' => 'Transaction refunded successfully'
        ]);
    }

    /**
     * Add attachment to transaction
     */
    public function addAttachment(Request $request, Transaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'attachment_url' => 'required|string|max:500',
            'attachment_name' => 'required|string|max:255'
        ]);

        $transaction->addAttachment($validated['attachment_url'], $validated['attachment_name']);

        return response()->json([
            'success' => true,
            'data' => $transaction,
            'message' => 'Attachment added successfully'
        ]);
    }

    /**
     * Get revenue transactions
     */
    public function revenue(Request $request): JsonResponse
    {
        $query = Transaction::revenue()->with(['franchise', 'unit', 'user']);

        // Apply filters
        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'message' => 'Revenue transactions retrieved successfully'
        ]);
    }

    /**
     * Get expense transactions
     */
    public function expenses(Request $request): JsonResponse
    {
        $query = Transaction::expenses()->with(['franchise', 'unit', 'user']);

        // Apply filters
        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'message' => 'Expense transactions retrieved successfully'
        ]);
    }

    /**
     * Get transaction statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Transaction::query();

        // Apply filters
        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $stats = [
            'total_transactions' => $query->count(),
            'total_revenue' => $query->revenue()->sum('amount'),
            'total_expenses' => $query->expenses()->sum('amount'),
            'net_profit' => $query->revenue()->sum('amount') - $query->expenses()->sum('amount'),
            'pending_transactions' => $query->pending()->count(),
            'completed_transactions' => $query->completed()->count(),
            'recurring_transactions' => $query->recurring()->count(),
            'transactions_by_type' => $query->groupBy('type')
                ->selectRaw('type, count(*) as count, sum(amount) as total_amount')
                ->get()
                ->keyBy('type'),
            'transactions_by_category' => $query->groupBy('category')
                ->selectRaw('category, count(*) as count, sum(amount) as total_amount')
                ->get()
                ->keyBy('category'),
            'monthly_revenue' => $query->revenue()
                ->selectRaw('YEAR(transaction_date) as year, MONTH(transaction_date) as month, sum(amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
            'monthly_expenses' => $query->expenses()
                ->selectRaw('YEAR(transaction_date) as year, MONTH(transaction_date) as month, sum(amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Transaction statistics retrieved successfully'
        ]);
    }

    /**
     * Get current user's transactions (for franchise owners)
     */
    public function myTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('owner_id', $user->id)->first();

        if (!$franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user'
            ], 404);
        }

        $transactions = Transaction::where('franchise_id', $franchise->id)
            ->with(['franchise', 'unit'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'message' => 'Transactions retrieved successfully'
        ]);
    }

    /**
     * Get current user's unit transactions (for unit managers)
     */
    public function myUnitTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('manager_id', $user->id)->first();

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user'
            ], 404);
        }

        $transactions = Transaction::where('unit_id', $unit->id)
            ->with(['franchise', 'unit'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'message' => 'Unit transactions retrieved successfully'
        ]);
    }
}
