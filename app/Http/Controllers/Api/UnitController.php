<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Unit::with(['franchise', 'franchisee']);

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

        if ($request->has('franchisee_id')) {
            $query->where('franchisee_id', $request->franchisee_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('unit_name', 'like', "%{$search}%")
                    ->orWhere('unit_code', 'like', "%{$search}%")
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
            'message' => 'Units retrieved successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_name' => 'required|string|max:255',
            'unit_code' => 'nullable|string|max:50|unique:units,unit_code',
            'franchise_id' => 'required|exists:franchises,id',
            'unit_type' => 'nullable|in:store,kiosk,mobile,online,warehouse,office',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state_province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'franchisee_id' => 'nullable|exists:users,id',
            'size_sqft' => 'nullable|numeric|min:0',
            'initial_investment' => 'nullable|numeric|min:0',
            'lease_start_date' => 'nullable|date',
            'lease_end_date' => 'nullable|date|after:lease_start_date',
            'opening_date' => 'nullable|date',
            'status' => 'required|in:planning,construction,training,active,temporarily_closed,permanently_closed',
            'employee_count' => 'nullable|integer|min:0',
            'monthly_revenue' => 'nullable|numeric|min:0',
            'monthly_expenses' => 'nullable|numeric|min:0',
            'operating_hours' => 'nullable|array',
            'amenities' => 'nullable|array',
            'equipment' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Generate unit_code if not provided
        if (empty($validated['unit_code'])) {
            $validated['unit_code'] = $this->generateUniqueUnitCode($validated['unit_name'], $validated['franchise_id']);
        }

        $unit = Unit::create($validated);

        return response()->json([
            'success' => true,
            'data' => $unit->load(['franchise', 'franchisee']),
            'message' => 'Unit created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit): JsonResponse
    {
        $unit->load(['franchise', 'franchisee', 'tasks']);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'unit_name' => 'sometimes|string|max:255',
            'franchise_id' => 'sometimes|exists:franchises,id',
            'unit_type' => 'sometimes|in:store,kiosk,mobile,online,warehouse,office',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'state_province' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'franchisee_id' => 'nullable|exists:users,id',
            'size_sqft' => 'nullable|numeric|min:0',
            'initial_investment' => 'nullable|numeric|min:0',
            'opening_date' => 'nullable|date',
            'status' => 'sometimes|in:planning,construction,training,active,temporarily_closed,permanently_closed',
            'lease_start_date' => 'nullable|date',
            'lease_end_date' => 'nullable|date|after:lease_start_date',
            'monthly_revenue' => 'nullable|numeric|min:0',
            'monthly_expenses' => 'nullable|numeric|min:0',
            'operating_hours' => 'nullable|array',
            'amenities' => 'nullable|array',
            'equipment' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $unit->update($validated);

        return response()->json([
            'success' => true,
            'data' => $unit->load(['franchise', 'franchisee']),
            'message' => 'Unit updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit): JsonResponse
    {
        // Check if unit has active tasks
        if ($unit->tasks()->where('status', 'in_progress')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete unit with active tasks',
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully',
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
            'monthly_revenue' => $unit->monthly_revenue ?: 0,
            'monthly_expenses' => $unit->monthly_expenses ?: 0,
            'monthly_profit' => $unit->monthly_profit ?: 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Unit statistics retrieved successfully',
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
            'message' => 'Unit activated successfully',
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
            'message' => 'Unit deactivated successfully',
        ]);
    }

    /**
     * Close unit
     */
    public function close(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'closing_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $unit->update([
            'status' => 'closed',
            'closing_date' => $validated['closing_date'],
            'notes' => $validated['notes'] ?? $unit->notes,
        ]);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit closed successfully',
        ]);
    }

    /**
     * Get current user's units (for franchise owners)
     */
    public function myUnits(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user',
            ], 404);
        }

        $units = $franchise->units()
            ->with(['franchise', 'franchisee', 'tasks'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $units,
            'message' => 'Units retrieved successfully',
        ]);
    }

    /**
     * Get current user's unit (for unit managers)
     */
    public function myUnit(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)
            ->with(['franchise', 'franchisee', 'tasks'])
            ->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit retrieved successfully',
        ]);
    }

    /**
     * Get statistics for current user's unit
     */
    public function myUnitStatistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
        }

        $stats = [
            'total_tasks' => $unit->tasks()->count(),
            'completed_tasks' => $unit->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $unit->tasks()->where('status', 'pending')->count(),
            'in_progress_tasks' => $unit->tasks()->where('status', 'in_progress')->count(),
            'monthly_revenue' => $unit->monthly_revenue ?: 0,
            'monthly_expenses' => $unit->monthly_expenses ?: 0,
            'monthly_profit' => $unit->monthly_profit ?: 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Unit statistics retrieved successfully',
        ]);
    }

    /**
     * Generate a unique unit code based on unit name and franchise
     */
    private function generateUniqueUnitCode(string $unitName, int $franchiseId): string
    {
        // Get franchise info for prefix
        $franchise = Franchise::find($franchiseId);
        $prefix = $franchise ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $franchise->name), 0, 3)) : 'UNI';
        if (empty($prefix)) {
            $prefix = 'UNI';
        }

        // Create base code from unit name
        $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $unitName), 0, 6));
        if (empty($baseCode)) {
            $baseCode = 'UNIT';
        }

        $code = $prefix.'-'.$baseCode;
        $counter = 1;

        // Ensure uniqueness
        while (Unit::where('unit_code', $code)->exists()) {
            $code = $prefix.'-'.$baseCode.$counter;
            $counter++;
        }

        return $code;
    }
}
