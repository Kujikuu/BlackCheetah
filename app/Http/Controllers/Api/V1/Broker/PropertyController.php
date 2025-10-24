<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Broker;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends BaseResourceController
{
    /**
     * Display a listing of the broker's properties.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::with(['broker'])
            ->where('broker_id', auth()->id());

        // Apply search
        $this->applySearch($query, $request, ['title', 'description', 'city', 'address']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->has('state_province')) {
            $query->where('state_province', $request->state_province);
        }

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        // Rent range filter
        if ($request->has('min_rent')) {
            $query->where('monthly_rent', '>=', $request->min_rent);
        }

        if ($request->has('max_rent')) {
            $query->where('monthly_rent', '<=', $request->max_rent);
        }

        // Size range filter
        if ($request->has('min_size')) {
            $query->where('size_sqm', '>=', $request->min_size);
        }

        if ($request->has('max_size')) {
            $query->where('size_sqm', '<=', $request->max_size);
        }

        // Apply sorting
        $sort = $this->parseSortParams($request, 'created_at');
        $query->orderBy($sort['column'], $sort['order']);

        // Pagination
        $perPage = $this->getPaginationParams($request);
        $properties = $query->paginate($perPage);

        return $this->successResponse($properties, 'Properties retrieved successfully');
    }

    /**
     * Store a newly created property.
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['broker_id'] = auth()->id();

        $property = Property::create($data);

        return $this->successResponse(
            $property->load(['broker']),
            'Property created successfully',
            201
        );
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property): JsonResponse
    {
        // Ensure broker can only view their own properties
        if ($property->broker_id !== auth()->id()) {
            return $this->forbiddenResponse('You do not have permission to view this property');
        }

        $property->load(['broker']);

        return $this->successResponse($property, 'Property retrieved successfully');
    }

    /**
     * Update the specified property.
     */
    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        // Ensure broker can only update their own properties
        if ($property->broker_id !== auth()->id()) {
            return $this->forbiddenResponse('You do not have permission to update this property');
        }

        $property->update($request->validated());

        return $this->successResponse(
            $property->load(['broker']),
            'Property updated successfully'
        );
    }

    /**
     * Remove the specified property.
     */
    public function destroy(Property $property): JsonResponse
    {
        // Ensure broker can only delete their own properties
        if ($property->broker_id !== auth()->id()) {
            return $this->forbiddenResponse('You do not have permission to delete this property');
        }

        $property->delete();

        return $this->successResponse(null, 'Property deleted successfully');
    }

    /**
     * Bulk delete properties.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:properties,id',
        ]);

        // Only delete properties belonging to the authenticated broker
        $deleted = Property::whereIn('id', $validated['ids'])
            ->where('broker_id', auth()->id())
            ->delete();

        return $this->successResponse(
            ['deleted_count' => $deleted],
            'Properties deleted successfully'
        );
    }

    /**
     * Mark property as leased.
     */
    public function markLeased(Property $property): JsonResponse
    {
        // Ensure broker can only update their own properties
        if ($property->broker_id !== auth()->id()) {
            return $this->forbiddenResponse('You do not have permission to update this property');
        }

        $property->update(['status' => 'leased']);

        return $this->successResponse(
            $property->load(['broker']),
            'Property marked as leased successfully'
        );
    }
}

