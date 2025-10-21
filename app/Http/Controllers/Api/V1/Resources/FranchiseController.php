<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class FranchiseController extends Controller
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
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $franchises = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $franchises,
            'message' => 'Franchises retrieved successfully'
        ]);
    }

    /**
 * Store a newly created resource in storage.
 */
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'franchisor_id' => 'required|exists:users,id',
        'business_name' => 'required|string|max:255',
        'brand_name' => 'nullable|string|max:255',
        'industry' => 'required|string|max:255',
        'description' => 'nullable|string',
        'website' => 'nullable|url',
        'logo' => 'nullable|string|max:255',
        'business_registration_number' => 'required|string|unique:franchises,business_registration_number',
        'tax_id' => 'nullable|string|max:255',
        'business_type' => 'required|in:corporation,llc,partnership,sole_proprietorship',
        'established_date' => 'nullable|date',
        'headquarters_country' => 'required|string|max:100',
        'headquarters_city' => 'required|string|max:100',
        'headquarters_address' => 'required|string',
        'contact_phone' => 'required|string|max:20',
        'contact_email' => 'required|email|max:255|unique:franchises,contact_email',
        'franchise_fee' => 'nullable|numeric|min:0',
        'royalty_percentage' => 'nullable|numeric|min:0|max:100',
        'marketing_fee_percentage' => 'nullable|numeric|min:0|max:100',
        'status' => 'required|in:active,inactive,pending_approval,suspended',
        'plan' => 'nullable|string|max:255',
        'business_hours' => 'nullable|array',
        'social_media' => 'nullable|array',
        'documents' => 'nullable|array'
    ]);

    $franchise = Franchise::create($validated);

    return response()->json([
        'success' => true,
        'data' => $franchise->load(['franchisor', 'units']),
        'message' => 'Franchise created successfully'
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(Franchise $franchise): JsonResponse
    {
        $franchise->load(['owner', 'units', 'leads', 'tasks', 'transactions', 'royalties', 'revenues']);

        return response()->json([
            'success' => true,
            'data' => $franchise,
            'message' => 'Franchise retrieved successfully'
        ]);
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

    return response()->json([
        'success' => true,
        'data' => $franchise->load(['franchisor', 'units']),
        'message' => 'Franchise updated successfully'
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Franchise $franchise): JsonResponse
    {
        // Check if franchise has active units
        if ($franchise->units()->where('status', 'active')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete franchise with active units'
            ], 422);
        }

        $franchise->delete();

        return response()->json([
            'success' => true,
            'message' => 'Franchise deleted successfully'
        ]);
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

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Franchise statistics retrieved successfully'
        ]);
    }

    /**
     * Activate franchise
     */
    public function activate(Franchise $franchise): JsonResponse
    {
        $franchise->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'data' => $franchise,
            'message' => 'Franchise activated successfully'
        ]);
    }

    /**
     * Deactivate franchise
     */
    public function deactivate(Franchise $franchise): JsonResponse
    {
        $franchise->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'data' => $franchise,
            'message' => 'Franchise deactivated successfully'
        ]);
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
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $franchise,
            'message' => 'Franchise retrieved successfully'
        ]);
    }

    /**
     * Get statistics for current user's franchise
     */
    public function myStatistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('owner_id', $user->id)->first();

        if (!$franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user'
            ], 404);
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

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Franchise statistics retrieved successfully'
        ]);
    }
}
