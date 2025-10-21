<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Manually verify user credentials to avoid guard-related issues
            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $token = $user->createToken('API Token')->plainTextToken;

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Define user abilities based on role
            $userAbilityRules = [];
            if ($user->role === 'admin') {
                $userAbilityRules = [
                    ['action' => 'manage', 'subject' => 'all'],
                ];
            } elseif ($user->role === 'franchisor') {
                $userAbilityRules = [
                    ['action' => 'read', 'subject' => 'FranchisorDashboard'],
                    ['action' => 'read', 'subject' => 'Lead'],
                    ['action' => 'manage', 'subject' => 'Lead'],
                    ['action' => 'manage', 'subject' => 'User'],
                    ['action' => 'read', 'subject' => 'Franchise'],
                    ['action' => 'read', 'subject' => 'Unit'],
                    ['action' => 'manage', 'subject' => 'Task'],
                    ['action' => 'read', 'subject' => 'Performance'],
                    ['action' => 'read', 'subject' => 'Revenue'],
                    ['action' => 'manage', 'subject' => 'Royalty'],
                    ['action' => 'create', 'subject' => 'TechnicalRequest'],
                ];
            } elseif ($user->role === 'franchisee') {
                $userAbilityRules = [
                    ['action' => 'read', 'subject' => 'FranchiseeDashboard'],
                    ['action' => 'read', 'subject' => 'Unit'],
                    ['action' => 'read', 'subject' => 'Task'],
                    ['action' => 'read', 'subject' => 'Performance'],
                    ['action' => 'read', 'subject' => 'Revenue'],
                    ['action' => 'read', 'subject' => 'Royalty'],
                    ['action' => 'create', 'subject' => 'TechnicalRequest'],
                ];
            } elseif ($user->role === 'sales') {
                $userAbilityRules = [
                    ['action' => 'manage', 'subject' => 'Lead'],
                    ['action' => 'read', 'subject' => 'Task'],
                    ['action' => 'create', 'subject' => 'TechnicalRequest'],
                ];
            }

            return response()->json([
                'accessToken' => $token,
                'userData' => [
                    'id' => $user->id,
                    'fullName' => $user->name,
                    'username' => $user->email,
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
                'userAbilityRules' => $userAbilityRules,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'required|in:franchisor,franchisee,sales',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'pending',
            ]);

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                    ],
                    'token' => $token,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                        'avatar' => $user->avatar,
                        'phone' => $user->phone,
                        'city' => $user->city,
                        'country' => $user->country,
                        'last_login_at' => $user->last_login_at,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
