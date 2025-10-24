<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Broker;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Models\Franchise;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BrokerController extends BaseResourceController
{
    /**
     * Get franchises assigned to the authenticated broker
     */
    public function getAssignedFranchises(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Get all franchises where this broker is assigned
            $franchises = Franchise::where('broker_id', $user->id)
                ->with(['franchisor:id,name,email'])
                ->get()
                ->map(function ($franchise) {
                    return [
                        'id' => $franchise->id,
                        'brand_name' => $franchise->brand_name,
                        'business_name' => $franchise->business_name,
                        'industry' => $franchise->industry,
                        'status' => $franchise->status,
                        'is_marketplace_listed' => $franchise->is_marketplace_listed,
                        'logo' => $franchise->logo,
                        'franchise_fee' => $franchise->franchise_fee,
                        'royalty_percentage' => $franchise->royalty_percentage,
                        'established_date' => $franchise->established_date,
                        'total_units' => $franchise->total_units,
                        'active_units' => $franchise->active_units,
                        'franchisor' => $franchise->franchisor ? [
                            'id' => $franchise->franchisor->id,
                            'name' => $franchise->franchisor->name,
                            'email' => $franchise->franchisor->email,
                        ] : null,
                    ];
                });

            return $this->successResponse([
                'data' => $franchises,
                'total' => $franchises->count(),
            ], 'Assigned franchises retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch assigned franchises', $e->getMessage(), 500);
        }
    }

    /**
     * Toggle marketplace listing status for an assigned franchise
     */
    public function toggleMarketplaceListing(Franchise $franchise): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify that the franchise is assigned to this broker
            if ($franchise->broker_id !== $user->id) {
                return $this->forbiddenResponse('You do not have permission to modify this franchise');
            }

            $franchise->update([
                'is_marketplace_listed' => !$franchise->is_marketplace_listed,
            ]);

            return $this->successResponse(
                [
                    'franchise_id' => $franchise->id,
                    'is_marketplace_listed' => $franchise->is_marketplace_listed,
                ],
                $franchise->is_marketplace_listed
                    ? 'Franchise is now listed in marketplace'
                    : 'Franchise removed from marketplace'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update marketplace listing', $e->getMessage(), 500);
        }
    }
}

