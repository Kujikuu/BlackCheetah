<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use App\Models\Franchise;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class RevenueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Revenue::with(['franchise', 'unit', 'user', 'verifiedBy', 'parentRevenue']);

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

        if ($request->has('payment_status')) {
            $query->byPaymentStatus($request->payment_status);
        }

        if ($request->has('franchise_id')) {
            $query->byFranchise($request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->byUnit($request->unit_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('is_verified')) {
            $query->verified();
        }

        if ($request->has('is_recurring')) {
            $query->recurring();
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        if ($request->has('period_year') && $request->has('period_month')) {
            $query->byPeriod($request->period_year, $request->period_month);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('revenue_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'revenue_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $revenues = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $revenues,
            'message' => 'Revenues retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:sales,fees,royalties,commissions,other',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'required|string|max:500',
            'revenue_date' => 'required|date',
            'period_year' => 'required|integer|min:2020|max:2030',
            'period_month' => 'required|integer|min:1|max:12',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'user_id' => 'required|exists:users,id',
            'source' => 'nullable|string|max:100',
            'customer_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'required|in:pending,paid,partial,failed,refunded',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric',
            'line_items' => 'nullable|array',
            'metadata' => 'nullable|array',
            'status' => 'required|in:pending,verified,disputed,cancelled',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_end_date' => 'nullable|date|after:revenue_date',
            'parent_revenue_id' => 'nullable|exists:revenues,id',
            'attachments' => 'nullable|array',
            'is_auto_generated' => 'boolean'
        ]);

        $revenue = Revenue::create($validated);

        return response()->json([
            'success' => true,
            'data' => $revenue->load(['franchise', 'unit', 'user']),
            'message' => 'Revenue created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Revenue $revenue): JsonResponse
    {
        $revenue->load(['franchise', 'unit', 'user', 'verifiedBy', 'parentRevenue', 'childRevenues']);

        return response()->json([
            'success' => true,
            'data' => $revenue,
            'message' => 'Revenue retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Revenue $revenue): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:sales,fees,royalties,commissions,other',
            'category' => 'sometimes|string|max:100',
            'amount' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'description' => 'sometimes|string|max:500',
            'revenue_date' => 'sometimes|date',
            'period_year' => 'sometimes|integer|min:2020|max:2030',
            'period_month' => 'sometimes|integer|min:1|max:12',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'user_id' => 'sometimes|exists:users,id',
            'source' => 'nullable|string|max:100',
            'customer_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'sometimes|in:pending,paid,partial,failed,refunded',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric',
            'line_items' => 'nullable|array',
            'metadata' => 'nullable|array',
            'status' => 'sometimes|in:pending,verified,disputed,cancelled',
            'notes' => 'nullable|string',
            'is_recurring' => 'sometimes|boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_end_date' => 'nullable|date|after:revenue_date',
            'attachments' => 'nullable|array'
        ]);

        $revenue->update($validated);

        return response()->json([
            'success' => true,
            'data' => $revenue->load(['franchise', 'unit', 'user']),
            'message' => 'Revenue updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Revenue $revenue): JsonResponse
    {
        // Check if revenue can be deleted
        if ($revenue->status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete verified revenue'
            ], 422);
        }

        $revenue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Revenue deleted successfully'
        ]);
    }

    /**
     * Verify revenue
     */
    public function verify(Request $request, Revenue $revenue): JsonResponse
    {
        $validated = $request->validate([
            'verification_notes' => 'nullable|string'
        ]);

        $revenue->verify($validated['verification_notes'] ?? null);

        return response()->json([
            'success' => true,
            'data' => $revenue,
            'message' => 'Revenue verified successfully'
        ]);
    }

    /**
     * Dispute revenue
     */
    public function dispute(Request $request, Revenue $revenue): JsonResponse
    {
        $validated = $request->validate([
            'dispute_reason' => 'required|string|max:500'
        ]);

        $revenue->dispute($validated['dispute_reason']);

        return response()->json([
            'success' => true,
            'data' => $revenue,
            'message' => 'Revenue disputed successfully'
        ]);
    }

    /**
     * Refund revenue
     */
    public function refund(Request $request, Revenue $revenue): JsonResponse
    {
        $validated = $request->validate([
            'refund_amount' => 'nullable|numeric|min:0|max:' . $revenue->amount,
            'refund_reason' => 'required|string|max:500'
        ]);

        $refundRevenue = $revenue->refund(
            $validated['refund_amount'] ?? null,
            $validated['refund_reason']
        );

        return response()->json([
            'success' => true,
            'data' => $refundRevenue,
            'message' => 'Revenue refunded successfully'
        ]);
    }

    /**
     * Add line item to revenue
     */
    public function addLineItem(Request $request, Revenue $revenue): JsonResponse
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0'
        ]);

        $revenue->addLineItem(
            $validated['item_name'],
            $validated['quantity'],
            $validated['unit_price'],
            $validated['total_price']
        );

        return response()->json([
            'success' => true,
            'data' => $revenue,
            'message' => 'Line item added successfully'
        ]);
    }

    /**
     * Add attachment to revenue
     */
    public function addAttachment(Request $request, Revenue $revenue): JsonResponse
    {
        $validated = $request->validate([
            'attachment_url' => 'required|string|max:500',
            'attachment_name' => 'required|string|max:255'
        ]);

        $revenue->addAttachment($validated['attachment_url'], $validated['attachment_name']);

        return response()->json([
            'success' => true,
            'data' => $revenue,
            'message' => 'Attachment added successfully'
        ]);
    }

    /**
     * Get sales revenues
     */
    public function sales(Request $request): JsonResponse
    {
        $query = Revenue::sales()->with(['franchise', 'unit', 'user']);

        // Apply filters
        if ($request->has('franchise_id')) {
            $query->byFranchise($request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->byUnit($request->unit_id);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $revenues = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $revenues,
            'message' => 'Sales revenues retrieved successfully'
        ]);
    }

    /**
     * Get fees revenues
     */
    public function fees(Request $request): JsonResponse
    {
        $query = Revenue::fees()->with(['franchise', 'unit', 'user']);

        // Apply filters
        if ($request->has('franchise_id')) {
            $query->byFranchise($request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->byUnit($request->unit_id);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $revenues = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $revenues,
            'message' => 'Fee revenues retrieved successfully'
        ]);
    }

    /**
     * Get revenue statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Revenue::query();

        // Apply filters
        if ($request->has('franchise_id')) {
            $query->byFranchise($request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->byUnit($request->unit_id);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $stats = [
            'total_revenues' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'total_net_amount' => $query->sum('net_amount'),
            'verified_revenues' => $query->verified()->count(),
            'verified_amount' => $query->verified()->sum('amount'),
            'pending_revenues' => $query->pending()->count(),
            'pending_amount' => $query->pending()->sum('amount'),
            'disputed_revenues' => $query->disputed()->count(),
            'disputed_amount' => $query->disputed()->sum('amount'),
            'recurring_revenues' => $query->recurring()->count(),
            'auto_generated_revenues' => $query->autoGenerated()->count(),
            'revenues_by_type' => $query->groupBy('type')
                ->selectRaw('type, count(*) as count, sum(amount) as total_amount')
                ->get()
                ->keyBy('type'),
            'revenues_by_category' => $query->groupBy('category')
                ->selectRaw('category, count(*) as count, sum(amount) as total_amount')
                ->get()
                ->keyBy('category'),
            'revenues_by_payment_status' => $query->groupBy('payment_status')
                ->selectRaw('payment_status, count(*) as count, sum(amount) as total_amount')
                ->get()
                ->keyBy('payment_status'),
            'monthly_revenues' => $query->selectRaw('period_year, period_month, count(*) as count, sum(amount) as total_amount')
                ->groupBy('period_year', 'period_month')
                ->orderBy('period_year', 'desc')
                ->orderBy('period_month', 'desc')
                ->limit(12)
                ->get(),
            'average_revenue_amount' => $query->avg('amount'),
            'verification_rate' => $query->count() > 0 ? 
                ($query->verified()->count() / $query->count()) * 100 : 0
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Revenue statistics retrieved successfully'
        ]);
    }

    /**
     * Get revenue breakdown by category
     */
    public function breakdown(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period_year' => 'required|integer|min:2020|max:2030',
            'period_month' => 'nullable|integer|min:1|max:12',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id'
        ]);

        $breakdown = Revenue::getRevenueBreakdownByCategory(
            $validated['period_year'],
            $validated['period_month'] ?? null,
            $validated['franchise_id'] ?? null,
            $validated['unit_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => $breakdown,
            'message' => 'Revenue breakdown retrieved successfully'
        ]);
    }

    /**
     * Get total revenue by period
     */
    public function totalByPeriod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period_year' => 'required|integer|min:2020|max:2030',
            'period_month' => 'nullable|integer|min:1|max:12',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id'
        ]);

        $total = Revenue::getTotalRevenueByPeriod(
            $validated['period_year'],
            $validated['period_month'] ?? null,
            $validated['franchise_id'] ?? null,
            $validated['unit_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => $total,
                'period' => [
                    'year' => $validated['period_year'],
                    'month' => $validated['period_month'] ?? null
                ],
                'filters' => [
                    'franchise_id' => $validated['franchise_id'] ?? null,
                    'unit_id' => $validated['unit_id'] ?? null
                ]
            ],
            'message' => 'Total revenue by period retrieved successfully'
        ]);
    }

    /**
     * Get current user's revenues (for franchise owners)
     */
    public function myRevenues(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('owner_id', $user->id)->first();

        if (!$franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user'
            ], 404);
        }

        $revenues = Revenue::where('franchise_id', $franchise->id)
            ->with(['franchise', 'unit'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $revenues,
            'message' => 'Revenues retrieved successfully'
        ]);
    }

    /**
     * Get current user's unit revenues (for unit managers)
     */
    public function myUnitRevenues(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('manager_id', $user->id)->first();

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user'
            ], 404);
        }

        $revenues = Revenue::where('unit_id', $unit->id)
            ->with(['franchise', 'unit'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $revenues,
            'message' => 'Unit revenues retrieved successfully'
        ]);
    }
}
