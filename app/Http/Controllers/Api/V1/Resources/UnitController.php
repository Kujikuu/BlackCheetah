<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Franchise;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends BaseResourceController
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

        return $this->successResponse($units, 'Units retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Generate unit_code if not provided
        if (empty($validated['unit_code'])) {
            $validated['unit_code'] = $this->generateUniqueUnitCode($validated['unit_name'], $validated['franchise_id']);
        }

        $unit = Unit::create($validated);

        return $this->successResponse($unit->load(['franchise', 'franchisee']), 'Unit created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit): JsonResponse
    {
        $unit->load(['franchise', 'franchisee', 'tasks']);

        return $this->successResponse($unit, 'Unit retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, Unit $unit): JsonResponse
    {
        $validated = $request->validated();

        $unit->update($validated);

        return $this->successResponse($unit->load(['franchise', 'franchisee']), 'Unit updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit): JsonResponse
    {
        // Check if unit has active tasks
        if ($unit->tasks()->where('status', 'in_progress')->exists()) {
            return $this->validationErrorResponse(null, 'Cannot delete unit with active tasks');
        }

        $unit->delete();

        return $this->successResponse(null, 'Unit deleted successfully');
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

        return $this->successResponse($stats, 'Unit statistics retrieved successfully');
    }

    /**
     * Activate unit
     */
    public function activate(Unit $unit): JsonResponse
    {
        $unit->update(['status' => 'active']);

        return $this->successResponse($unit, 'Unit activated successfully');
    }

    /**
     * Deactivate unit
     */
    public function deactivate(Unit $unit): JsonResponse
    {
        $unit->update(['status' => 'inactive']);

        return $this->successResponse($unit, 'Unit deactivated successfully');
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

        return $this->successResponse($unit, 'Unit closed successfully');
    }

    /**
     * Get current user's units (for franchise owners)
     */
    public function myUnits(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return $this->notFoundResponse('No franchise found for current user');
        }

        $units = $franchise->units()
            ->with(['franchise', 'franchisee', 'tasks'])
            ->paginate(15);

        return $this->successResponse($units, 'Units retrieved successfully');
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
            return $this->notFoundResponse('No unit found for current user');
        }

        return $this->successResponse($unit, 'Unit retrieved successfully');
    }

    /**
     * Get statistics for current user's unit
     */
    public function myUnitStatistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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

        return $this->successResponse($stats, 'Unit statistics retrieved successfully');
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

        $code = $prefix . '-' . $baseCode;
        $counter = 1;

        // Ensure uniqueness
        while (Unit::where('unit_code', $code)->exists()) {
            $code = $prefix . '-' . $baseCode . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Get staff members for a unit
     */
    public function getStaff($unitId): JsonResponse
    {
        try {
            $unit = Unit::findOrFail($unitId);

            // Get staff with pivot data
            $staff = $unit->staff()->withPivot(['role', 'assigned_date', 'is_primary'])->get();

            // Transform the data
            $transformedStaff = $staff->map(function ($staffMember) {
                return [
                    'id' => $staffMember->id,
                    'name' => $staffMember->name,
                    'email' => $staffMember->email,
                    'phone' => $staffMember->phone,
                    'jobTitle' => $staffMember->job_title,
                    'department' => $staffMember->department,
                    'status' => $staffMember->status,
                    'employmentType' => $staffMember->employment_type,
                    'hireDate' => $staffMember->hire_date,
                    'shiftTime' => $staffMember->shift_start && $staffMember->shift_end
                        ? $staffMember->shift_start->format('g:i A') . ' - ' . $staffMember->shift_end->format('g:i A')
                        : 'Not set',
                    'role' => $staffMember->pivot->role,
                    'assignedDate' => $staffMember->pivot->assigned_date,
                    'isPrimary' => $staffMember->pivot->is_primary,
                ];
            });

            return $this->successResponse($transformedStaff);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch staff data', $e->getMessage(), 500);
        }
    }
}
