<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Franchise;
use App\Models\Unit;
use App\Models\Lead;
use App\Models\Task;
use App\Models\TechnicalRequest;
use App\Models\Transaction;
use App\Models\Revenue;
use App\Models\Royalty;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function dashboardStats(): JsonResponse
    {
        try {
            // User statistics
            $totalUsers = User::count();
            $franchisors = User::where('role', 'franchisor')->count();
            $franchisees = User::where('role', 'franchisee')->count();
            $salesUsers = User::where('role', 'sales')->count();

            // Calculate growth percentages (compared to last month)
            $lastMonth = Carbon::now()->subMonth();
            
            $totalUsersLastMonth = User::where('created_at', '<=', $lastMonth)->count();
            $franchisorsLastMonth = User::where('role', 'franchisor')->where('created_at', '<=', $lastMonth)->count();
            $franchiseesLastMonth = User::where('role', 'franchisee')->where('created_at', '<=', $lastMonth)->count();
            $salesUsersLastMonth = User::where('role', 'sales')->where('created_at', '<=', $lastMonth)->count();

            $totalUsersGrowth = $totalUsersLastMonth > 0 ? (($totalUsers - $totalUsersLastMonth) / $totalUsersLastMonth) * 100 : 0;
            $franchisorsGrowth = $franchisorsLastMonth > 0 ? (($franchisors - $franchisorsLastMonth) / $franchisorsLastMonth) * 100 : 0;
            $franchiseesGrowth = $franchiseesLastMonth > 0 ? (($franchisees - $franchiseesLastMonth) / $franchiseesLastMonth) * 100 : 0;
            $salesUsersGrowth = $salesUsersLastMonth > 0 ? (($salesUsers - $salesUsersLastMonth) / $salesUsersLastMonth) * 100 : 0;

            $stats = [
                [
                    'title' => 'Total Users',
                    'value' => number_format($totalUsers),
                    'change' => round($totalUsersGrowth, 1),
                    'desc' => 'All registered users',
                    'icon' => 'tabler-users',
                    'iconColor' => 'primary'
                ],
                [
                    'title' => 'Franchisors',
                    'value' => number_format($franchisors),
                    'change' => round($franchisorsGrowth, 1),
                    'desc' => 'Active franchisors',
                    'icon' => 'tabler-building-store',
                    'iconColor' => 'success'
                ],
                [
                    'title' => 'Franchisees',
                    'value' => number_format($franchisees),
                    'change' => round($franchiseesGrowth, 1),
                    'desc' => 'Active franchisees',
                    'icon' => 'tabler-user-check',
                    'iconColor' => 'info'
                ],
                [
                    'title' => 'Sales Users',
                    'value' => number_format($salesUsers),
                    'change' => round($salesUsersGrowth, 1),
                    'desc' => 'Sales team members',
                    'icon' => 'tabler-chart-line',
                    'iconColor' => 'warning'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent users for dashboard
     */
    public function recentUsers(): JsonResponse
    {
        try {
            $recentUsers = User::with(['franchise'])
                ->whereIn('role', ['franchisor', 'franchisee', 'sales'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($user) {
                    $userData = [
                        'id' => $user->id,
                        'fullName' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                        'avatar' => $user->avatar,
                        'joinedDate' => $user->created_at->format('Y-m-d'),
                        'lastLogin' => $user->last_login_at ? $user->last_login_at->format('Y-m-d') : null,
                        'phone' => $user->phone,
                    ];

                    // Add role-specific data
                    if ($user->role === 'franchisor' && $user->franchise) {
                        $userData['franchiseName'] = $user->franchise->name;
                        $userData['plan'] = $user->franchise->plan ?? 'Basic';
                    } elseif ($user->role === 'franchisee') {
                        $userData['location'] = $user->city;
                    } elseif ($user->role === 'sales') {
                        $userData['city'] = $user->city;
                    }

                    return $userData;
                });

            return response()->json([
                'success' => true,
                'data' => $recentUsers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart data for admin dashboard
     */
    public function chartData(): JsonResponse
    {
        try {
            // User registration chart data (last 12 months)
            $userChartData = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $count = User::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                
                $userChartData[] = [
                    'month' => $month->format('M Y'),
                    'users' => $count
                ];
            }

            // Revenue chart data (last 12 months)
            $revenueChartData = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $revenue = Revenue::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount');
                
                $revenueChartData[] = [
                    'month' => $month->format('M Y'),
                    'revenue' => $revenue
                ];
            }

            // Technical requests chart data (last 12 months)
            $requestsChartData = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $requests = TechnicalRequest::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                
                $requestsChartData[] = [
                    'month' => $month->format('M Y'),
                    'requests' => $requests
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $userChartData,
                    'revenue' => $revenueChartData,
                    'requests' => $requestsChartData
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
            $sortBy = $request->get('sortBy', 'created_at');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

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

            return response()->json([
                'success' => true,
                'data' => $franchisors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchisors',
                'error' => $e->getMessage()
            ], 500);
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
            $sortBy = $request->get('sortBy', 'created_at');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

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

            return response()->json([
                'success' => true,
                'data' => $franchisees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchisees',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all sales users
     */
    public function salesUsers(Request $request): JsonResponse
    {
        try {
            $query = User::where('role', 'sales');

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
            $sortBy = $request->get('sortBy', 'created_at');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

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

            return response()->json([
                'success' => true,
                'data' => $salesUsers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|in:franchisor,franchisee,sales',
                'phone' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:100',
                'status' => 'required|in:active,pending,inactive',
                'franchiseName' => 'required_if:role,franchisor|string|max:255',
                'plan' => 'required_if:role,franchisor|in:Basic,Pro,Enterprise',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->fullName,
                'email' => $request->email,
                'password' => Hash::make('password123'), // Default password
                'role' => $request->role,
                'status' => $request->status,
                'phone' => $request->phone,
                'city' => $request->city,
            ]);

            // Create franchise if user is franchisor
            if ($request->role === 'franchisor') {
                Franchise::create([
                    'name' => $request->franchiseName,
                    'franchisor_id' => $user->id,
                    'plan' => $request->plan,
                    'status' => 'active',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'fullName' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'joinedDate' => $user->created_at->format('Y-m-d'),
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a user
     */
    public function updateUser(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:100',
                'status' => 'required|in:active,pending,inactive',
                'franchiseName' => 'required_if:role,franchisor|string|max:255',
                'plan' => 'required_if:role,franchisor|in:Basic,Pro,Enterprise',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update user
            $user->update([
                'name' => $request->fullName,
                'email' => $request->email,
                'status' => $request->status,
                'phone' => $request->phone,
                'city' => $request->city,
            ]);

            // Update franchise if user is franchisor
            if ($user->role === 'franchisor' && $user->franchise) {
                $user->franchise->update([
                    'name' => $request->franchiseName,
                    'plan' => $request->plan,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'fullName' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
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

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
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
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password',
                'error' => $e->getMessage()
            ], 500);
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
            $sortBy = $request->get('sortBy', 'created_at');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

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

            return response()->json([
                'success' => true,
                'data' => $requests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch technical requests',
                'error' => $e->getMessage()
            ], 500);
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
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $technicalRequest->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Technical request status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update technical request status',
                'error' => $e->getMessage()
            ], 500);
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

            return response()->json([
                'success' => true,
                'message' => 'Technical request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete technical request',
                'error' => $e->getMessage()
            ], 500);
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
                round((($totalFranchisors - $totalFranchisorsLastMonth) / $totalFranchisorsLastMonth) * 100, 1) : 
                ($totalFranchisors > 0 ? 100 : 0);
            $basicGrowth = $basicPlanLastMonth > 0 ? 
                round((($basicPlan - $basicPlanLastMonth) / $basicPlanLastMonth) * 100, 1) : 
                ($basicPlan > 0 ? 100 : 0);
            $proGrowth = $proPlanLastMonth > 0 ? 
                round((($proPlan - $proPlanLastMonth) / $proPlanLastMonth) * 100, 1) : 
                ($proPlan > 0 ? 100 : 0);

            $stats = [
                [
                    'title' => 'Total Franchisors',
                    'value' => (string) $totalFranchisors,
                    'change' => $totalGrowth,
                    'desc' => 'All registered franchisors',
                    'icon' => 'tabler-building-store',
                    'iconColor' => 'primary'
                ],
                [
                    'title' => 'Basic Plan',
                    'value' => (string) $basicPlan,
                    'change' => $basicGrowth,
                    'desc' => 'Basic plan subscribers',
                    'icon' => 'tabler-user-check',
                    'iconColor' => 'success'
                ],
                [
                    'title' => 'Pro Plan',
                    'value' => (string) $proPlan,
                    'change' => $proGrowth,
                    'desc' => 'Pro plan subscribers',
                    'icon' => 'tabler-crown',
                    'iconColor' => 'warning'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchisor stats',
                'error' => $e->getMessage()
            ], 500);
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
                round((($totalFranchisees - $totalFranchiseesLastMonth) / $totalFranchiseesLastMonth) * 100, 1) : 
                ($totalFranchisees > 0 ? 100 : 0);
            $activeGrowth = $activeFranchiseesLastMonth > 0 ? 
                round((($activeFranchisees - $activeFranchiseesLastMonth) / $activeFranchiseesLastMonth) * 100, 1) : 
                ($activeFranchisees > 0 ? 100 : 0);
            $pendingGrowth = $pendingApprovalLastMonth > 0 ? 
                round((($pendingApproval - $pendingApprovalLastMonth) / $pendingApprovalLastMonth) * 100, 1) : 
                ($pendingApproval > 0 ? 100 : 0);

            $stats = [
                [
                    'title' => 'Total Franchisees',
                    'value' => (string) $totalFranchisees,
                    'change' => $totalGrowth,
                    'desc' => 'All registered franchisees',
                    'icon' => 'tabler-users',
                    'iconColor' => 'primary'
                ],
                [
                    'title' => 'Active Franchisees',
                    'value' => (string) $activeFranchisees,
                    'change' => $activeGrowth,
                    'desc' => 'Currently active',
                    'icon' => 'tabler-user-check',
                    'iconColor' => 'success'
                ],
                [
                    'title' => 'Pending Approval',
                    'value' => (string) $pendingApproval,
                    'change' => $pendingGrowth,
                    'desc' => 'Awaiting verification',
                    'icon' => 'tabler-clock',
                    'iconColor' => 'warning'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchisee stats',
                'error' => $e->getMessage()
            ], 500);
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
            $totalSalesUsers = User::where('role', 'sales')->count();
            $activeSales = User::where('role', 'sales')
                ->where('status', 'active')->count();
            $pendingApproval = User::where('role', 'sales')
                ->where('status', 'pending')->count();

            // Last month counts
            $totalSalesUsersLastMonth = User::where('role', 'sales')
                ->where('created_at', '<=', $lastMonth)->count();
            $activeSalesLastMonth = User::where('role', 'sales')
                ->where('status', 'active')
                ->where('created_at', '<=', $lastMonth)->count();
            $pendingApprovalLastMonth = User::where('role', 'sales')
                ->where('status', 'pending')
                ->where('created_at', '<=', $lastMonth)->count();

            // Calculate growth percentages
            $totalGrowth = $totalSalesUsersLastMonth > 0 ? 
                round((($totalSalesUsers - $totalSalesUsersLastMonth) / $totalSalesUsersLastMonth) * 100, 1) : 
                ($totalSalesUsers > 0 ? 100 : 0);
            $activeGrowth = $activeSalesLastMonth > 0 ? 
                round((($activeSales - $activeSalesLastMonth) / $activeSalesLastMonth) * 100, 1) : 
                ($activeSales > 0 ? 100 : 0);
            $pendingGrowth = $pendingApprovalLastMonth > 0 ? 
                round((($pendingApproval - $pendingApprovalLastMonth) / $pendingApprovalLastMonth) * 100, 1) : 
                ($pendingApproval > 0 ? 100 : 0);

            $stats = [
                [
                    'title' => 'Total Sales Users',
                    'value' => (string) $totalSalesUsers,
                    'change' => $totalGrowth,
                    'desc' => 'All sales team members',
                    'icon' => 'tabler-chart-line',
                    'iconColor' => 'primary'
                ],
                [
                    'title' => 'Active Sales',
                    'value' => (string) $activeSales,
                    'change' => $activeGrowth,
                    'desc' => 'Currently active',
                    'icon' => 'tabler-user-check',
                    'iconColor' => 'success'
                ],
                [
                    'title' => 'Pending Approval',
                    'value' => (string) $pendingApproval,
                    'change' => $pendingGrowth,
                    'desc' => 'Awaiting verification',
                    'icon' => 'tabler-clock',
                    'iconColor' => 'warning'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}