<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Franchise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Unit::with(['franchise', 'manager']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        if ($request->has('manager_id')) {
            $query->where('manager_id', $request->manager_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unit_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $units = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $units,
            'message' => 'Units retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'franchise_id' => 'required|exists:franchises,id',
            'type' => 'required|in:restaurant,retail,office,warehouse,kiosk,mobile,other',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'size_sqft' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'opening_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after:opening_date',
            'status' => 'required|in:planning,construction,active,inactive,closed,renovating',
            'lease_start_date' => 'nullable|date',
            'lease_end_date' => 'nullable|date|after:lease_start_date',
            'monthly_rent' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'equipment_list' => 'nullable|array',
            'operating_hours' => 'nullable|array',
            'special_features' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $unit = Unit::create($validated);

        return response()->json([
            'success' => true,
            'data' => $unit->load(['franchise', 'manager']),
            'message' => 'Unit created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit): JsonResponse
    {
        $unit->load(['franchise', 'manager', 'tasks', 'transactions', 'revenues']);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'franchise_id' => 'sometimes|exists:franchises,id',
            'type' => 'sometimes|in:restaurant,retail,office,warehouse,kiosk,mobile,other',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'size_sqft' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'opening_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after:opening_date',
            'status' => 'sometimes|in:planning,construction,active,inactive,closed,renovating',
            'lease_start_date' => 'nullable|date',
            'lease_end_date' => 'nullable|date|after:lease_start_date',
            'monthly_rent' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'equipment_list' => 'nullable|array',
            'operating_hours' => 'nullable|array',
            'special_features' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $unit->update($validated);

        return response()->json([
            'success' => true,
            'data' => $unit->load(['franchise', 'manager']),
            'message' => 'Unit updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit): JsonResponse
    {
        // Check if unit has active transactions or tasks
        if ($unit->transactions()->where('status', 'pending')->exists() || 
            $unit->tasks()->where('status', 'in_progress')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete unit with active transactions or tasks'
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully'
        ]);
    }

    /**
     * Get unit statistics
     */
    public function statistics(Unit $unit): JsonResponse
    {
        $stats = [
            'total_tasks' => $unit->tasks()->count(),
            'completed_tasks' => $unit->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $unit->tasks()->where('status', 'pending')->count(),
            'total_revenue' => $unit->revenues()->sum('amount'),
            'monthly_revenue' => $unit->revenues()
                ->whereYear('revenue_date', now()->year)
                ->whereMonth('revenue_date', now()->month)
                ->sum('amount'),
            'total_transactions' => $unit->transactions()->count(),
            'revenue_transactions' => $unit->transactions()->where('type', 'revenue')->sum('amount'),
            'expense_transactions' => $unit->transactions()->where('type', 'expense')->sum('amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Unit statistics retrieved successfully'
        ]);
    }

    /**
     * Activate unit
     */
    public function activate(Unit $unit): JsonResponse
    {
        $unit->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit activated successfully'
        ]);
    }

    /**
     * Deactivate unit
     */
    public function deactivate(Unit $unit): JsonResponse
    {
        $unit->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit deactivated successfully'
        ]);
    }

    /**
     * Close unit
     */
    public function close(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'closing_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $unit->update([
            'status' => 'closed',
            'closing_date' => $validated['closing_date'],
            'notes' => $validated['notes'] ?? $unit->notes
        ]);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit closed successfully'
        ]);
    }

    /**
     * Get current user's units (for franchise owners)
     */
    public function myUnits(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('owner_id', $user->id)->first();

        if (!$franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user'
            ], 404);
        }

        $units = $franchise->units()
            ->with(['franchise', 'manager', 'tasks', 'transactions'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $units,
            'message' => 'Units retrieved successfully'
        ]);
    }

    /**
     * Get current user's unit (for unit managers)
     */
    public function myUnit(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('manager_id', $user->id)
            ->with(['franchise', 'manager', 'tasks', 'transactions'])
            ->first();

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit retrieved successfully'
        ]);
    }

    /**
     * Get statistics for current user's unit
     */
    public function myUnitStatistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('manager_id', $user->id)->first();

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user'
            ], 404);
        }

        $stats = [
            'total_tasks' => $unit->tasks()->count(),
            'completed_tasks' => $unit->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $unit->tasks()->where('status', 'pending')->count(),
            'in_progress_tasks' => $unit->tasks()->where('status', 'in_progress')->count(),
            'total_transactions' => $unit->transactions()->count(),
            'completed_transactions' => $unit->transactions()->where('status', 'completed')->count(),
            'total_revenue' => $unit->transactions()->where('type', 'revenue')->sum('amount'),
            'total_expenses' => $unit->transactions()->where('type', 'expense')->sum('amount'),
            'monthly_revenue' => $unit->transactions()
                ->where('type', 'revenue')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount'),
            'monthly_expenses' => $unit->transactions()
                ->where('type', 'expense')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Unit statistics retrieved successfully'
        ]);
    }
}
