<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Http\Requests\StoreRevenueRequest;
use App\Http\Requests\UpdateRevenueRequest;
use App\Models\Revenue;
use App\Models\Franchise;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class RevenueController extends BaseResourceController
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
        $perPage = $this->getPaginationParams($request);
        $revenues = $query->paginate($perPage);

        return $this->successResponse($revenues, 'Revenues retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRevenueRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $revenue = Revenue::create($validated);

        return $this->successResponse($revenue->load(['franchise', 'unit', 'user']), 'Revenue created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Revenue $revenue): JsonResponse
    {
        $revenue->load(['franchise', 'unit', 'user', 'verifiedBy', 'parentRevenue', 'childRevenues']);

        return $this->successResponse($revenue, 'Revenue retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRevenueRequest $request, Revenue $revenue): JsonResponse
    {
        $validated = $request->validated();

        $revenue->update($validated);

        return $this->successResponse($revenue->load(['franchise', 'unit', 'user']), 'Revenue updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Revenue $revenue): JsonResponse
    {
        // Check if revenue can be deleted
        if ($revenue->status === 'verified') {
            return $this->validationErrorResponse(['status' => ['Cannot delete verified revenue']]);
        }

        $revenue->delete();

        return $this->successResponse(null, 'Revenue deleted successfully');
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

        return $this->successResponse($revenue, 'Revenue verified successfully');
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

        return $this->successResponse($revenue, 'Revenue disputed successfully');
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

        return $this->successResponse($refundRevenue, 'Revenue refunded successfully');
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

        return $this->successResponse($revenue, 'Line item added successfully');
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

        return $this->successResponse($revenue, 'Attachment added successfully');
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

        $perPage = $this->getPaginationParams($request);
        $revenues = $query->paginate($perPage);

        return $this->successResponse($revenues, 'Sales revenues retrieved successfully');
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

        $perPage = $this->getPaginationParams($request);
        $revenues = $query->paginate($perPage);

        return $this->successResponse($revenues, 'Fee revenues retrieved successfully');
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

        return $this->successResponse($stats, 'Revenue statistics retrieved successfully');
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

        return $this->successResponse($breakdown, 'Revenue breakdown retrieved successfully');
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

        return $this->successResponse([
            'total_revenue' => $total,
            'period' => [
                'year' => $validated['period_year'],
                'month' => $validated['period_month'] ?? null
            ],
            'filters' => [
                'franchise_id' => $validated['franchise_id'] ?? null,
                'unit_id' => $validated['unit_id'] ?? null
            ]
        ], 'Total revenue by period retrieved successfully');
    }

    /**
     * Get current user's revenues (for franchise owners)
     */
    public function myRevenues(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('owner_id', $user->id)->first();

        if (!$franchise) {
            return $this->notFoundResponse('No franchise found for current user');
        }

        $revenues = Revenue::where('franchise_id', $franchise->id)
            ->with(['franchise', 'unit'])
            ->paginate($this->getPaginationParams($request));

        return $this->successResponse($revenues, 'Revenues retrieved successfully');
    }

    /**
     * Get current user's unit revenues (for unit managers)
     */
    public function myUnitRevenues(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('manager_id', $user->id)->first();

        if (!$unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        $revenues = Revenue::where('unit_id', $unit->id)
            ->with(['franchise', 'unit'])
            ->paginate($this->getPaginationParams($request));

        return $this->successResponse($revenues, 'Unit revenues retrieved successfully');
    }
}
