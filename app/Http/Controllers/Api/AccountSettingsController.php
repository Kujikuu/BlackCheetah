<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AccountSettingsController extends Controller
{
    /**
     * Get current user profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar ? asset('uploads/'.$user->avatar) : null,
                'role' => $user->role,
                'status' => $user->status,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'gender' => $user->gender,
                'country' => $user->country,
                'state' => $user->state,
                'city' => $user->city,
                'address' => $user->address,
                'preferences' => $user->preferences ?? [
                    'timezone' => '(GMT+03:00) Riyadh',
                    'language' => 'en',
                    'notifications' => [],
                ],
            ],
            'message' => 'Profile retrieved successfully',
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            // Email is excluded from updates - users cannot change their email
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'preferences' => ['nullable', 'array'],
            'preferences.timezone' => ['nullable', 'string'],
            'preferences.language' => ['nullable', 'string', 'in:en,ar'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Remove email from data if somehow it was included
        unset($data['email']);

        // Merge preferences with existing ones
        if (isset($data['preferences'])) {
            $data['preferences'] = array_merge($user->preferences ?? [], $data['preferences']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar ? asset('uploads/'.$user->avatar) : null,
                'role' => $user->role,
                'status' => $user->status,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'gender' => $user->gender,
                'country' => $user->country,
                'state' => $user->state,
                'city' => $user->city,
                'address' => $user->address,
                'preferences' => $user->preferences,
            ],
            'message' => 'Profile updated successfully',
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify current password
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the avatar file
        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:800'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! $request->hasFile('avatar')) {
            return response()->json([
                'success' => false,
                'message' => 'No avatar file was uploaded',
            ], 422);
        }

        $file = $request->file('avatar');

        if (! $file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'The uploaded file is invalid',
            ], 422);
        }

        try {
            // Delete old avatar if exists (check both disks for backward compatibility)
            if ($user->avatar) {
                if (Storage::disk('uploads')->exists($user->avatar)) {
                    Storage::disk('uploads')->delete($user->avatar);
                } elseif (Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
            }

            // Store new avatar with unique filename using 'uploads' disk (no symlink needed)
            $extension = $file->getClientOriginalExtension();
            $filename = 'avatar_'.time().'_'.$user->id.'.'.$extension;
            $path = $file->storeAs('avatars', $filename, 'uploads');

            // Update user record
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'data' => [
                    'avatar' => asset('uploads/'.$path),
                    'avatar_path' => $path,
                ],
                'message' => 'Avatar uploaded successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            // Try both disks for backward compatibility
            if (Storage::disk('uploads')->exists($user->avatar)) {
                Storage::disk('uploads')->delete($user->avatar);
            } elseif (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->update(['avatar' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Avatar deleted successfully',
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'notifications' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Merge with existing preferences
        $preferences = $user->preferences ?? [];
        $preferences['notifications'] = $request->notifications;

        $user->update(['preferences' => $preferences]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'preferences' => $user->preferences,
            ],
            'message' => 'Notification preferences updated successfully',
        ]);
    }
}
