<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Http\Requests\StoreFranchiseRequest;
use App\Http\Requests\UpdateFranchiseRequest;
use App\Models\Franchise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class FranchiseController extends BaseResourceController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Franchise::with(['owner', 'units']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('region')) {
            $query->where('region', $request->region);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('franchise_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sort = $this->parseSortParams($request, 'created_at');
        $query->orderBy($sort['column'], $sort['order']);

        // Pagination
        $perPage = $this->getPaginationParams($request);
        $franchises = $query->paginate($perPage);

        return $this->successResponse($franchises, 'Franchises retrieved successfully');
    }

    /**
 * Store a newly created resource in storage.
 */
public function store(StoreFranchiseRequest $request): JsonResponse
{
    $franchise = Franchise::create($request->validated());

    return $this->successResponse(
        $franchise->load(['franchisor', 'units']),
        'Franchise created successfully',
        201
    );
}

    /**
     * Display the specified resource.
     */
    public function show(Franchise $franchise): JsonResponse
    {
        $franchise->load(['owner', 'units', 'leads', 'tasks', 'transactions', 'royalties', 'revenues']);

        return $this->successResponse($franchise, 'Franchise retrieved successfully');
    }

    /**
 * Update the specified resource in storage.
 */
public function update(Request $request, Franchise $franchise): JsonResponse
{
    $validated = $request->validate([
        'franchisor_id' => 'sometimes|exists:users,id',
        'business_name' => 'sometimes|string|max:255',
        'brand_name' => 'sometimes|string|max:255',
        'industry' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'website' => 'sometimes|url',
        'logo' => 'sometimes|string|max:255',
        'business_registration_number' => ['sometimes', 'string', Rule::unique('franchises')->ignore($franchise->id)],
        'tax_id' => 'sometimes|string|max:255',
        'business_type' => 'sometimes|in:corporation,llc,partnership,sole_proprietorship',
        'established_date' => 'sometimes|date',
        'headquarters_country' => 'sometimes|string|max:100',
        'headquarters_city' => 'sometimes|string|max:100',
        'headquarters_address' => 'sometimes|string',
        'contact_phone' => 'sometimes|string|max:20',
        'contact_email' => ['sometimes', 'email', 'max:255', Rule::unique('franchises')->ignore($franchise->id)],
        'franchise_fee' => 'sometimes|numeric|min:0',
        'royalty_percentage' => 'sometimes|numeric|min:0|max:100',
        'marketing_fee_percentage' => 'sometimes|numeric|min:0|max:100',
        'status' => 'sometimes|in:active,inactive,pending_approval,suspended',
        'plan' => 'sometimes|string|max:255',
        'business_hours' => 'sometimes|array',
        'social_media' => 'sometimes|array',
        'documents' => 'sometimes|array'
    ]);

    $franchise->update($validated);

    return $this->successResponse($franchise->load(['franchisor', 'units']), 'Franchise updated successfully');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Franchise $franchise): JsonResponse
    {
        // Check if franchise has active units
        if ($franchise->units()->where('status', 'active')->exists()) {
            return $this->validationErrorResponse(['status' => ['Cannot delete franchise with active units']]);
        }

        $franchise->delete();

        return $this->successResponse(null, 'Franchise deleted successfully');
    }

    /**
     * Get franchise statistics
     */
    public function statistics(Franchise $franchise): JsonResponse
    {
        $stats = [
            'total_units' => $franchise->units()->count(),
            'active_units' => $franchise->units()->where('status', 'active')->count(),
            'total_leads' => $franchise->leads()->count(),
            'converted_leads' => $franchise->leads()->where('status', 'converted')->count(),
            'total_revenue' => $franchise->revenues()->sum('amount'),
            'monthly_revenue' => $franchise->revenues()
                ->whereYear('revenue_date', now()->year)
                ->whereMonth('revenue_date', now()->month)
                ->sum('amount'),
            'pending_royalties' => $franchise->royalties()->where('status', 'pending')->sum('total_amount'),
            'overdue_royalties' => $franchise->royalties()->where('status', 'overdue')->sum('total_amount')
        ];

        return $this->successResponse($stats, 'Franchise statistics retrieved successfully');
    }

    /**
     * Activate franchise
     */
    public function activate(Franchise $franchise): JsonResponse
    {
        $franchise->update(['status' => 'active']);

        return $this->successResponse($franchise, 'Franchise activated successfully');
    }

    /**
     * Deactivate franchise
     */
    public function deactivate(Franchise $franchise): JsonResponse
    {
        $franchise->update(['status' => 'inactive']);

        return $this->successResponse($franchise, 'Franchise deactivated successfully');
    }

    /**
     * Get current user's franchise (for franchise owners)
     */
    public function myFranchise(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('owner_id', $user->id)
            ->with(['units', 'leads', 'transactions', 'royalties'])
            ->first();

        if (!$franchise) {
            return $this->notFoundResponse('No franchise found for current user');
        }

        return $this->successResponse($franchise, 'Franchise retrieved successfully');
    }

    /**
     * Get statistics for current user's franchise
     */
    public function myStatistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('owner_id', $user->id)->first();

        if (!$franchise) {
            return $this->notFoundResponse('No franchise found for current user');
        }

        $stats = [
            'total_units' => $franchise->units()->count(),
            'active_units' => $franchise->units()->where('status', 'active')->count(),
            'total_leads' => $franchise->leads()->count(),
            'converted_leads' => $franchise->leads()->where('status', 'converted')->count(),
            'total_transactions' => $franchise->transactions()->count(),
            'completed_transactions' => $franchise->transactions()->where('status', 'completed')->count(),
            'total_revenue' => $franchise->transactions()->where('type', 'revenue')->sum('amount'),
            'total_expenses' => $franchise->transactions()->where('type', 'expense')->sum('amount'),
            'pending_royalties' => $franchise->royalties()->where('status', 'pending')->sum('total_amount'),
            'overdue_royalties' => $franchise->royalties()->where('status', 'overdue')->sum('total_amount')
        ];

        return $this->successResponse($stats, 'Franchise statistics retrieved successfully');
    }
}
