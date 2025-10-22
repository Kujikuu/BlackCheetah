<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\CompleteProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    /**
     * Check if the current user needs to complete their profile.
     */
    public function checkOnboardingStatus(): JsonResponse
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'franchisee') {
            return $this->successResponse([
                'requires_onboarding' => false,
            ], 'User is not a franchisee');
        }

        return $this->successResponse([
            'requires_onboarding' => ! $user->profile_completed,
            'profile_completed' => $user->profile_completed,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'nationality' => $user->nationality,
                'state' => $user->state,
                'city' => $user->city,
                'address' => $user->address,
            ],
        ]);
    }

    /**
     * Complete the franchisee's profile during onboarding.
     */
    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            /** @var User $user */
            $user = Auth::user();

            $user->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'nationality' => $request->nationality,
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address,
                'profile_completed' => true,
            ]);

            DB::commit();

            return $this->successResponse([
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'nationality' => $user->nationality,
                    'state' => $user->state,
                    'city' => $user->city,
                    'address' => $user->address,
                    'profile_completed' => $user->profile_completed,
                ],
            ], 'Profile completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to complete profile', $e->getMessage(), 500);
        }
    }
}
