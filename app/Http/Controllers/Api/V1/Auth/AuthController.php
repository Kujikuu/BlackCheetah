<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
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
                'remember' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Manually verify user credentials to avoid guard-related issues
            $user = \App\Models\User::where('email', $request->email)->first();

            // Check if account is locked
            if ($user && $user->isLocked()) {
                $remainingTime = $user->remainingLockTime();
                return $this->errorResponse(
                    "Your account has been temporarily locked due to multiple failed login attempts. Please try again in {$remainingTime} minutes.",
                    null,
                    429
                );
            }

            if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                // Increment failed login attempts
                if ($user) {
                    $user->incrementFailedLoginAttempts();
                }

                return $this->unauthorizedResponse('Invalid credentials');
            }

            // Reset failed login attempts on successful login
            $user->resetFailedLoginAttempts();

            // Create token with expiration based on remember me
            $tokenName = 'API Token';
            $expiresAt = $request->input('remember', false) ? now()->addDays(30) : null;
            
            $token = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

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
                    ['action' => 'manage', 'subject' => 'Franchise'], // Allow franchise registration/management
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
            } elseif ($user->role === 'broker') {
                $userAbilityRules = [
                    ['action' => 'read', 'subject' => 'Dashboard'],
                    ['action' => 'read', 'subject' => 'BrokerDashboard'],
                    ['action' => 'manage', 'subject' => 'Brokerage'],
                    ['action' => 'manage', 'subject' => 'Lead'],
                    ['action' => 'manage', 'subject' => 'LeadManagement'],
                    ['action' => 'read', 'subject' => 'Task'],
                    ['action' => 'update', 'subject' => 'Task'],
                    ['action' => 'read', 'subject' => 'TechnicalRequest'],
                    ['action' => 'create', 'subject' => 'TechnicalRequest'],
                    ['action' => 'update', 'subject' => 'TechnicalRequest'],
                    ['action' => 'read', 'subject' => 'Statistics'],
                    ['action' => 'manage', 'subject' => 'Note'],
                    ['action' => 'manage', 'subject' => 'Property'],
                    ['action' => 'manage', 'subject' => 'Franchise'],
                ];
            }

            // Check if franchisor needs to complete franchise registration
            $requiresFranchiseRegistration = false;
            if ($user->role === 'franchisor') {
                $franchise = $user->franchise()->first();
                $requiresFranchiseRegistration = !$franchise;
            }

            return response()->json([
                'accessToken' => $token,
                'userData' => [
                    'id' => $user->id,
                    'fullName' => $user->name,
                    'username' => $user->email,
                    'avatar' => $user->avatar ? asset('uploads/'.$user->avatar) : null,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'nationality' => $user->nationality,
                ],
                'userAbilityRules' => $userAbilityRules,
                'requiresFranchiseRegistration' => $requiresFranchiseRegistration,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed', $e->getMessage(), 500);
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
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                ],
                'role' => 'required|in:franchisor,broker',
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*#?&).',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'pending',
            ]);

            // Send welcome email with verification link
            $user->sendEmailVerificationNotification();

            // Create token
            $token = $user->createToken('API Token')->plainTextToken;

            // Define user abilities based on role (same as login)
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
                    ['action' => 'manage', 'subject' => 'Franchise'],
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
            } elseif ($user->role === 'broker') {
                $userAbilityRules = [
                    ['action' => 'read', 'subject' => 'Dashboard'],
                    ['action' => 'read', 'subject' => 'BrokerDashboard'],
                    ['action' => 'manage', 'subject' => 'Brokerage'],
                    ['action' => 'manage', 'subject' => 'Lead'],
                    ['action' => 'manage', 'subject' => 'LeadManagement'],
                    ['action' => 'read', 'subject' => 'Task'],
                    ['action' => 'update', 'subject' => 'Task'],
                    ['action' => 'read', 'subject' => 'TechnicalRequest'],
                    ['action' => 'create', 'subject' => 'TechnicalRequest'],
                    ['action' => 'update', 'subject' => 'TechnicalRequest'],
                    ['action' => 'read', 'subject' => 'Statistics'],
                    ['action' => 'manage', 'subject' => 'Note'],
                    ['action' => 'manage', 'subject' => 'Property'],
                    ['action' => 'manage', 'subject' => 'Franchise'],
                ];
            }

            // Check if franchisor needs to complete franchise registration
            $requiresFranchiseRegistration = false;
            if ($user->role === 'franchisor') {
                $franchise = $user->franchise()->first();
                $requiresFranchiseRegistration = !$franchise;
            }

            // Return same format as login endpoint
            return response()->json([
                'accessToken' => $token,
                'userData' => [
                    'id' => $user->id,
                    'fullName' => $user->name,
                    'username' => $user->email,
                    'avatar' => $user->avatar ? asset('uploads/'.$user->avatar) : null,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'nationality' => $user->nationality,
                ],
                'userAbilityRules' => $userAbilityRules,
                'requiresFranchiseRegistration' => $requiresFranchiseRegistration,
                'message' => 'Registration successful. Please check your email to verify your account.',
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed', $e->getMessage(), 500);
        }
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed', $e->getMessage(), 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'avatar' => $user->avatar ? asset('uploads/'.$user->avatar) : null,
                    'phone' => $user->phone,
                    'city' => $user->city,
                    'nationality' => $user->nationality,
                    'last_login_at' => $user->last_login_at,
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get user data', $e->getMessage(), 500);
        }
    }
}
