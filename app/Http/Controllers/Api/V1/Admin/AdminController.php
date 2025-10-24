<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Admin\BaseAdminController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\TechnicalRequest;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\NewFranchiseeCredentials;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminController extends BaseAdminController
{
    /**
     * Get all franchisors
     */
    public function franchisors(Request $request): JsonResponse
    {
        try {
            $query = User::with(['franchise'])
                ->where('role', 'franchisor');

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('franchise', function ($fq) use ($search) {
                            $fq->where('name', 'like', "%{$search}%");
                        });
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

            // Paginate results
            $perPage = $request->get('perPage', 10);
            $franchisors = $query->paginate($perPage);

            // Transform data
            $franchisors->getCollection()->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'fullName' => $user->name,
                    'email' => $user->email,
                    'lastLogin' => $user->last_login_at ? $user->last_login_at->format('Y-m-d') : null,
                    'franchiseName' => $user->franchise ? $user->franchise->business_name : 'No Franchise',
                    'plan' => $user->franchise ? ($user->franchise->plan ?? 'Basic') : 'Basic',
                    'status' => $user->status,
                    'avatar' => $user->avatar,
                    'joinedDate' => $user->created_at->format('Y-m-d'),
                ];
            });

            return $this->successResponse($franchisors);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch franchisors', $e->getMessage(), 500);
        }
    }

    /**
     * Get all franchisees
     */
    public function franchisees(Request $request): JsonResponse
    {
        try {
            $query = User::where('role', 'franchisee');

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

            // Paginate results
            $perPage = $request->get('perPage', 10);
            $franchisees = $query->paginate($perPage);

            // Transform data
            $franchisees->getCollection()->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'fullName' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'location' => $user->city,
                    'status' => $user->status,
                    'avatar' => $user->avatar,
                    'joinedDate' => $user->created_at->format('Y-m-d'),
                ];
            });

            return $this->successResponse($franchisees);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch franchisees', $e->getMessage(), 500);
        }
    }

    /**
     * Get all brokers users
     */
    public function brokersUsers(Request $request): JsonResponse
    {
        try {
            $query = User::where('role', 'broker');

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

            // Paginate results
            $perPage = $request->get('perPage', 10);
            $salesUsers = $query->paginate($perPage);

            // Transform data
            $salesUsers->getCollection()->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'fullName' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'city' => $user->city,
                    'status' => $user->status,
                    'avatar' => $user->avatar,
                    'joinedDate' => $user->created_at->format('Y-m-d'),
                ];
            });

            return $this->successResponse($salesUsers);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch sales users', $e->getMessage(), 500);
        }
    }

    /**
     * Create a new user
     */
    public function createUser(CreateUserRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $validatedData['fullName'],
                'email' => $validatedData['email'],
                'password' => Hash::make('password123'), // Default password
                'role' => $validatedData['role'],
                'status' => $validatedData['status'],
                'phone' => $validatedData['phone'] ?? null,
                'city' => $validatedData['city'] ?? null,
            ]);

            // Create franchise if user is franchisor
            if ($validatedData['role'] === 'franchisor') {
                Franchise::create([
                    'business_name' => $validatedData['franchiseName'],
                    'franchisor_id' => $user->id,
                    'plan' => $validatedData['plan'],
                    'status' => 'active',
                ]);
            }

            DB::commit();

            return $this->successResponse([
                'id' => $user->id,
                'fullName' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'joinedDate' => $user->created_at->format('Y-m-d'),
            ], 'User created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create user', $e->getMessage(), 500);
        }
    }

    /**
     * Update a user
     */
    public function updateUser(UpdateUserRequest $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validatedData = $request->validated();

            DB::beginTransaction();

            // Update user
            $user->update([
                'name' => $validatedData['fullName'],
                'email' => $validatedData['email'],
                'status' => $validatedData['status'],
                'phone' => $validatedData['phone'] ?? null,
                'city' => $validatedData['city'] ?? null,
            ]);

            // Update franchise if user is franchisor
            if ($user->role === 'franchisor' && $user->franchise) {
                $user->franchise->update([
                    'business_name' => $validatedData['franchiseName'] ?? $user->franchise->business_name,
                    'plan' => $validatedData['plan'] ?? $user->franchise->plan,
                ]);
            }

            DB::commit();

            return $this->successResponse([
                'id' => $user->id,
                'fullName' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ], 'User updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to update user', $e->getMessage(), 500);
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            DB::beginTransaction();

            // Delete related franchise if user is franchisor
            if ($user->role === 'franchisor' && $user->franchise) {
                $user->franchise->delete();
            }

            // Delete user
            $user->delete();

            DB::commit();

            return $this->successResponse(null, 'User deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to delete user', $e->getMessage(), 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return $this->successResponse(null, 'Password reset successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reset password', $e->getMessage(), 500);
        }
    }

    /**
     * Get technical requests for admin
     */
    public function technicalRequests(Request $request): JsonResponse
    {
        try {
            $query = TechnicalRequest::with(['requester']);

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhereHas('requester', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply priority filter
            if ($request->has('priority') && $request->priority) {
                $query->where('priority', $request->priority);
            }

            // Apply sorting
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

            // Paginate results
            $perPage = $request->get('perPage', 10);
            $requests = $query->paginate($perPage);

            // Transform data
            $requests->getCollection()->transform(function ($request) {
                return [
                    'id' => $request->id,
                    'requestId' => 'TR-' . date('Y') . '-' . str_pad($request->id, 3, '0', STR_PAD_LEFT),
                    'userName' => $request->requester ? $request->requester->name : 'Unknown User',
                    'userEmail' => $request->requester ? $request->requester->email : 'Unknown Email',
                    'userAvatar' => $request->requester ? $request->requester->avatar : '',
                    'subject' => $request->title,
                    'description' => $request->description,
                    'priority' => $request->priority,
                    'status' => $request->status,
                    'date' => $request->created_at->format('Y-m-d'),
                    'category' => $request->category,
                    'attachments' => $request->attachments ?? [],
                ];
            });

            return $this->successResponse($requests);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch technical requests', $e->getMessage(), 500);
        }
    }

    /**
     * Update technical request status
     */
    public function updateTechnicalRequestStatus(Request $request, $id): JsonResponse
    {
        try {
            $technicalRequest = TechnicalRequest::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:open,in_progress,resolved,closed',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $technicalRequest->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

            return $this->successResponse(null, 'Technical request status updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update technical request status', $e->getMessage(), 500);
        }
    }

    /**
     * Delete technical request
     */
    public function deleteTechnicalRequest($id): JsonResponse
    {
        try {
            $technicalRequest = TechnicalRequest::findOrFail($id);
            $technicalRequest->delete();

            return $this->successResponse(null, 'Technical request deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete technical request', $e->getMessage(), 500);
        }
    }

    /**
     * Get franchisor statistics
     */
    public function franchisorStats(): JsonResponse
    {
        try {
            $lastMonth = Carbon::now()->subMonth();

            // Current counts
            $totalFranchisors = User::where('role', 'franchisor')->count();
            $basicPlan = User::where('role', 'franchisor')
                ->whereHas('franchise', function ($query) {
                    $query->where('plan', 'Basic');
                })->count();
            $proPlan = User::where('role', 'franchisor')
                ->whereHas('franchise', function ($query) {
                    $query->where('plan', 'Pro');
                })->count();

            // Last month counts
            $totalFranchisorsLastMonth = User::where('role', 'franchisor')
                ->where('created_at', '<=', $lastMonth)->count();
            $basicPlanLastMonth = User::where('role', 'franchisor')
                ->where('created_at', '<=', $lastMonth)
                ->whereHas('franchise', function ($query) {
                    $query->where('plan', 'Basic');
                })->count();
            $proPlanLastMonth = User::where('role', 'franchisor')
                ->where('created_at', '<=', $lastMonth)
                ->whereHas('franchise', function ($query) {
                    $query->where('plan', 'Pro');
                })->count();

            // Calculate growth percentages
            $totalGrowth = $totalFranchisorsLastMonth > 0 ?
                round((($totalFranchisors - $totalFranchisorsLastMonth) / $totalFranchisorsLastMonth) * 100, 1) : ($totalFranchisors > 0 ? 100 : 0);
            $basicGrowth = $basicPlanLastMonth > 0 ?
                round((($basicPlan - $basicPlanLastMonth) / $basicPlanLastMonth) * 100, 1) : ($basicPlan > 0 ? 100 : 0);
            $proGrowth = $proPlanLastMonth > 0 ?
                round((($proPlan - $proPlanLastMonth) / $proPlanLastMonth) * 100, 1) : ($proPlan > 0 ? 100 : 0);

            $stats = [
                [
                    'title' => 'Total Franchisors',
                    'value' => (string) $totalFranchisors,
                    'change' => $totalGrowth,
                    'desc' => 'All registered franchisors',
                    'icon' => 'tabler-building-store',
                    'iconColor' => 'primary',
                ],
                [
                    'title' => 'Basic Plan',
                    'value' => (string) $basicPlan,
                    'change' => $basicGrowth,
                    'desc' => 'Basic plan subscribers',
                    'icon' => 'tabler-user-check',
                    'iconColor' => 'success',
                ],
                [
                    'title' => 'Pro Plan',
                    'value' => (string) $proPlan,
                    'change' => $proGrowth,
                    'desc' => 'Pro plan subscribers',
                    'icon' => 'tabler-crown',
                    'iconColor' => 'warning',
                ],
            ];

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch franchisor stats', $e->getMessage(), 500);
        }
    }

    /**
     * Get franchisee statistics
     */
    public function franchiseeStats(): JsonResponse
    {
        try {
            $lastMonth = Carbon::now()->subMonth();

            // Current counts
            $totalFranchisees = User::where('role', 'franchisee')->count();
            $activeFranchisees = User::where('role', 'franchisee')
                ->where('status', 'active')->count();
            $pendingApproval = User::where('role', 'franchisee')
                ->where('status', 'pending')->count();

            // Last month counts
            $totalFranchiseesLastMonth = User::where('role', 'franchisee')
                ->where('created_at', '<=', $lastMonth)->count();
            $activeFranchiseesLastMonth = User::where('role', 'franchisee')
                ->where('status', 'active')
                ->where('created_at', '<=', $lastMonth)->count();
            $pendingApprovalLastMonth = User::where('role', 'franchisee')
                ->where('status', 'pending')
                ->where('created_at', '<=', $lastMonth)->count();

            // Calculate growth percentages
            $totalGrowth = $totalFranchiseesLastMonth > 0 ?
                round((($totalFranchisees - $totalFranchiseesLastMonth) / $totalFranchiseesLastMonth) * 100, 1) : ($totalFranchisees > 0 ? 100 : 0);
            $activeGrowth = $activeFranchiseesLastMonth > 0 ?
                round((($activeFranchisees - $activeFranchiseesLastMonth) / $activeFranchiseesLastMonth) * 100, 1) : ($activeFranchisees > 0 ? 100 : 0);
            $pendingGrowth = $pendingApprovalLastMonth > 0 ?
                round((($pendingApproval - $pendingApprovalLastMonth) / $pendingApprovalLastMonth) * 100, 1) : ($pendingApproval > 0 ? 100 : 0);

            $stats = [
                [
                    'title' => 'Total Franchisees',
                    'value' => (string) $totalFranchisees,
                    'change' => $totalGrowth,
                    'desc' => 'All registered franchisees',
                    'icon' => 'tabler-users',
                    'iconColor' => 'primary',
                ],
                [
                    'title' => 'Active Franchisees',
                    'value' => (string) $activeFranchisees,
                    'change' => $activeGrowth,
                    'desc' => 'Currently active',
                    'icon' => 'tabler-user-check',
                    'iconColor' => 'success',
                ],
                [
                    'title' => 'Pending Approval',
                    'value' => (string) $pendingApproval,
                    'change' => $pendingGrowth,
                    'desc' => 'Awaiting verification',
                    'icon' => 'tabler-clock',
                    'iconColor' => 'warning',
                ],
            ];

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch franchisee stats', $e->getMessage(), 500);
        }
    }

    /**
     * Get sales user statistics
     */
    public function salesStats(): JsonResponse
    {
        try {
            $lastMonth = Carbon::now()->subMonth();

            // Current counts
            $totalSalesUsers = User::where('role', 'broker')->count();
            $activeSales = User::where('role', 'broker')
                ->where('status', 'active')->count();
            $pendingApproval = User::where('role', 'broker')
                ->where('status', 'pending')->count();

            // Last month counts
            $totalSalesUsersLastMonth = User::where('role', 'broker')
                ->where('created_at', '<=', $lastMonth)->count();
            $activeSalesLastMonth = User::where('role', 'broker')
                ->where('status', 'active')
                ->where('created_at', '<=', $lastMonth)->count();
            $pendingApprovalLastMonth = User::where('role', 'broker')
                ->where('status', 'pending')
                ->where('created_at', '<=', $lastMonth)->count();

            // Calculate growth percentages
            $totalGrowth = $totalSalesUsersLastMonth > 0 ?
                round((($totalSalesUsers - $totalSalesUsersLastMonth) / $totalSalesUsersLastMonth) * 100, 1) : ($totalSalesUsers > 0 ? 100 : 0);
            $activeGrowth = $activeSalesLastMonth > 0 ?
                round((($activeSales - $activeSalesLastMonth) / $activeSalesLastMonth) * 100, 1) : ($activeSales > 0 ? 100 : 0);
            $pendingGrowth = $pendingApprovalLastMonth > 0 ?
                round((($pendingApproval - $pendingApprovalLastMonth) / $pendingApprovalLastMonth) * 100, 1) : ($pendingApproval > 0 ? 100 : 0);

            $stats = [
                [
                    'title' => 'Total Sales Users',
                    'value' => (string) $totalSalesUsers,
                    'change' => $totalGrowth,
                    'desc' => 'All sales team members',
                    'icon' => 'tabler-chart-line',
                    'iconColor' => 'primary',
                ],
                [
                    'title' => 'Active Sales',
                    'value' => (string) $activeSales,
                    'change' => $activeGrowth,
                    'desc' => 'Currently active',
                    'icon' => 'tabler-user-check',
                    'iconColor' => 'success',
                ],
                [
                    'title' => 'Pending Approval',
                    'value' => (string) $pendingApproval,
                    'change' => $pendingGrowth,
                    'desc' => 'Awaiting verification',
                    'icon' => 'tabler-clock',
                    'iconColor' => 'warning',
                ],
            ];

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch sales stats', $e->getMessage(), 500);
        }
    }

    /**
     * Create a franchisee user with an associated unit
     */
    public function createFranchiseeWithUnit(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                // Franchisee details
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',

                // Unit details
                'unit_name' => 'required|string|max:255',
                'franchise_id' => 'required|exists:franchises,id',
                'unit_type' => 'required|in:store,kiosk,mobile,online,warehouse,office',
                'address' => 'required|string',
                'city' => 'required|string|max:100',
                'state_province' => 'required|string|max:100',
                'postal_code' => 'required|string|max:20',
                'nationality' => 'required|string|max:100',
                'size_sqft' => 'nullable|numeric|min:0',
                'monthly_rent' => 'nullable|numeric|min:0',
                'opening_date' => 'nullable|date',
                'status' => 'required|in:planning,construction,training,active,temporarily_closed,permanently_closed',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            DB::beginTransaction();

            // Generate random password for franchisee
            $temporaryPassword = Str::random(12);

            // Create franchisee user
            $franchisee = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($temporaryPassword),
                'role' => 'franchisee',
                'status' => 'active',
                'phone' => $request->phone,
                'city' => $request->city,
                'profile_completed' => false, // Require onboarding
            ]);

            // Generate unit code
            $franchise = Franchise::find($request->franchise_id);
            $prefix = $franchise ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $franchise->name), 0, 3)) : 'UNI';
            if (empty($prefix)) {
                $prefix = 'UNI';
            }

            $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $request->unit_name), 0, 6));
            if (empty($baseCode)) {
                $baseCode = 'UNIT';
            }

            $unitCode = $prefix . '-' . $baseCode;
            $counter = 1;

            // Ensure uniqueness
            while (Unit::where('unit_code', $unitCode)->exists()) {
                $unitCode = $prefix . '-' . $baseCode . $counter;
                $counter++;
            }

            // Create associated unit with franchisee as manager
            $unit = Unit::create([
                'unit_name' => $request->unit_name,
                'unit_code' => $unitCode,
                'franchise_id' => $request->franchise_id,
                'franchisee_id' => $franchisee->id, // Assign the new franchisee as unit manager
                'unit_type' => $request->unit_type,
                'address' => $request->address,
                'city' => $request->city,
                'state_province' => $request->state_province,
                'postal_code' => $request->postal_code,
                'nationality' => $request->nationality,
                'phone' => $request->phone,
                'email' => $request->email,
                'size_sqft' => $request->size_sqft,
                'monthly_rent' => $request->monthly_rent,
                'opening_date' => $request->opening_date,
                'status' => $request->status,
                'employee_count' => 0,
            ]);

            // Send email notification with login credentials
            $loginUrl = env('APP_URL') . '/login';
            $franchisee->notify(new NewFranchiseeCredentials($temporaryPassword, $unitCode, $loginUrl));

            DB::commit();

            return $this->successResponse([
                'franchisee' => [
                    'id' => $franchisee->id,
                    'name' => $franchisee->name,
                    'email' => $franchisee->email,
                    'role' => $franchisee->role,
                    'status' => $franchisee->status,
                    'phone' => $franchisee->phone,
                    'city' => $franchisee->city,
                ],
                'unit' => [
                    'id' => $unit->id,
                    'unit_name' => $unit->unit_name,
                    'unit_code' => $unit->unit_code,
                    'unit_type' => $unit->unit_type,
                    'address' => $unit->address,
                    'city' => $unit->city,
                    'state_province' => $unit->state_province,
                    'status' => $unit->status,
                    'franchisee_id' => $unit->franchisee_id,
                ],
            ], 'Franchisee and unit created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create franchisee and unit', $e->getMessage(), 500);
        }
    }
}
