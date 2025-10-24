<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\StoreMarketplaceInquiryRequest;
use App\Models\Franchise;
use App\Models\MarketplaceInquiry;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Get all marketplace-listed franchises with filters.
     */
    public function getFranchises(Request $request): JsonResponse
    {
        $query = Franchise::with(['franchisor:id,name,email', 'broker:id,name,email'])
            ->where('status', 'active')
            ->where('is_marketplace_listed', true);

        // Apply search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by industry
        if ($request->has('industry')) {
            $query->where('industry', $request->industry);
        }

        // Filter by location
        if ($request->has('country')) {
            $query->where('headquarters_country', $request->country);
        }

        if ($request->has('city')) {
            $query->where('headquarters_city', $request->city);
        }

        // Franchise fee range
        if ($request->has('min_franchise_fee')) {
            $query->where('franchise_fee', '>=', $request->min_franchise_fee);
        }

        if ($request->has('max_franchise_fee')) {
            $query->where('franchise_fee', '<=', $request->max_franchise_fee);
        }

        // Filter by broker assignment
        if ($request->has('has_broker')) {
            if ($request->has_broker === 'true' || $request->has_broker === '1') {
                $query->whereNotNull('broker_id');
            } else {
                $query->whereNull('broker_id');
            }
        }

        // Sorting
        $sortBy = $request->get('sortBy', 'created_at');
        $orderBy = $request->get('orderBy', 'desc');
        $query->orderBy($sortBy, $orderBy);

        // Pagination
        $perPage = (int) $request->get('per_page', 12);
        $franchises = $query->paginate($perPage);

        return $this->successResponse($franchises, 'Franchises retrieved successfully');
    }

    /**
     * Get single franchise details.
     */
    public function getFranchiseDetails(int $id): JsonResponse
    {
        $franchise = Franchise::with([
            'franchisor:id,name,email,phone',
            'broker:id,name,email,phone',
            'units:id,franchise_id,unit_name,city,state_province,nationality,status'
        ])
            ->where('status', 'active')
            ->where('is_marketplace_listed', true)
            ->find($id);

        if (!$franchise) {
            return $this->notFoundResponse('Franchise not found or not available in marketplace');
        }

        return $this->successResponse($franchise, 'Franchise details retrieved successfully');
    }

    /**
     * Get all available properties with filters.
     */
    public function getProperties(Request $request): JsonResponse
    {
        $query = Property::with(['broker:id,name,email,phone'])
            ->where('status', 'available');

        // Apply search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by property type
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // Filter by location
        if ($request->has('country')) {
            $query->where('country', $request->country);
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
            $query->where('size_sqft', '>=', $request->min_size);
        }

        if ($request->has('max_size')) {
            $query->where('size_sqft', '<=', $request->max_size);
        }

        // Availability date filter
        if ($request->has('available_from')) {
            $query->where('available_from', '<=', $request->available_from);
        }

        // Sorting
        $sortBy = $request->get('sortBy', 'created_at');
        $orderBy = $request->get('orderBy', 'desc');
        $query->orderBy($sortBy, $orderBy);

        // Pagination
        $perPage = (int) $request->get('per_page', 12);
        $properties = $query->paginate($perPage);

        return $this->successResponse($properties, 'Properties retrieved successfully');
    }

    /**
     * Get single property details.
     */
    public function getPropertyDetails(int $id): JsonResponse
    {
        $property = Property::with(['broker:id,name,email,phone'])
            ->where('status', 'available')
            ->find($id);

        if (!$property) {
            return $this->notFoundResponse('Property not found or not available');
        }

        return $this->successResponse($property, 'Property details retrieved successfully');
    }

    /**
     * Submit marketplace inquiry.
     */
    public function submitInquiry(StoreMarketplaceInquiryRequest $request): JsonResponse
    {
        $inquiry = MarketplaceInquiry::create($request->validated());

        // Load relationships
        $inquiry->load([
            'franchise:id,brand_name,business_name',
            'property:id,title,city'
        ]);

        // TODO: Send notification to franchisor/broker about new inquiry
        // This can be implemented using Laravel notifications or events

        return $this->successResponse(
            $inquiry,
            'Your inquiry has been submitted successfully. We will contact you soon.',
            201
        );
    }
}

