<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
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
            return response()->json([
                'requires_onboarding' => false,
                'message' => 'User is not a franchisee',
            ]);
        }

        return response()->json([
            'requires_onboarding' => ! $user->profile_completed,
            'profile_completed' => $user->profile_completed,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country' => $user->country,
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
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address,
                'profile_completed' => true,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Profile completed successfully',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'state' => $user->state,
                    'city' => $user->city,
                    'address' => $user->address,
                    'profile_completed' => $user->profile_completed,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to complete profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
