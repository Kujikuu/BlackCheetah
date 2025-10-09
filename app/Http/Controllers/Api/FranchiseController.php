<?php

namespace App\Http\Controllers\Api;

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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:franchises,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'region' => 'required|string|max:100',
            'owner_id' => 'required|exists:users,id',
            'initial_fee' => 'required|numeric|min:0',
            'royalty_percentage' => 'required|numeric|min:0|max:100',
            'marketing_fee_percentage' => 'required|numeric|min:0|max:100',
            'territory_description' => 'nullable|string',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'required|date|after:contract_start_date',
            'status' => 'required|in:active,inactive,pending,terminated',
            'website' => 'nullable|url',
            'social_media' => 'nullable|array',
            'business_hours' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $franchise = Franchise::create($validated);

        return response()->json([
            'success' => true,
            'data' => $franchise->load(['owner', 'units']),
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
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('franchises')->ignore($franchise->id)],
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
            'region' => 'sometimes|string|max:100',
            'owner_id' => 'sometimes|exists:users,id',
            'initial_fee' => 'sometimes|numeric|min:0',
            'royalty_percentage' => 'sometimes|numeric|min:0|max:100',
            'marketing_fee_percentage' => 'sometimes|numeric|min:0|max:100',
            'territory_description' => 'nullable|string',
            'contract_start_date' => 'sometimes|date',
            'contract_end_date' => 'sometimes|date|after:contract_start_date',
            'status' => 'sometimes|in:active,inactive,pending,terminated',
            'website' => 'nullable|url',
            'social_media' => 'nullable|array',
            'business_hours' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $franchise->update($validated);

        return response()->json([
            'success' => true,
            'data' => $franchise->load(['owner', 'units']),
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
