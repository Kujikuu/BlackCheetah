<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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

class AdminController extends Controller
{
    /**
     * Parse sorting parameters from request
     * Handles both string and JSON object formats
     */
    private function parseSortParams(Request $request, string $defaultColumn = 'created_at', string $defaultOrder = 'desc'): array
    {
        $sortBy = $request->get('sortBy', $defaultColumn);

        // Handle if sortBy is a JSON string
        if (is_string($sortBy) && (str_starts_with($sortBy, '{') || str_starts_with($sortBy, '['))) {
            $sortByDecoded = json_decode($sortBy, true);
            if ($sortByDecoded && isset($sortByDecoded['key'])) {
                return [
                    'column' => $sortByDecoded['key'],
                    'order' => $sortByDecoded['order'] ?? $defaultOrder,
                ];
            }

            return ['column' => $defaultColumn, 'order' => $defaultOrder];
        }

        return [
            'column' => $sortBy,
            'order' => $request->get('sortOrder', $defaultOrder),
        ];
    }

    /**
     * Get theme color for primary
     */
    private function getPrimaryColor(): string
    {
        return '#696CFF'; // Default primary color
    }

    /**
     * Get theme color for success
     */
    private function getSuccessColor(): string
    {
        return '#71DD37'; // Default success color
    }

    /**
     * Get theme color for info
     */
    private function getInfoColor(): string
    {
        return '#03C3EC'; // Default info color
    }

    /**
     * Get theme color for warning
     */
    private function getWarningColor(): string
    {
        return '#FFAB00'; // Default warning color
    }

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

            // Calculate chart data for last 7 days
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $chartData[] = User::whereDate('created_at', $date->format('Y-m-d'))->count();
            }

            // Calculate franchisors chart data for last 7 days
            $franchisorsChartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $franchisorsChartData[] = User::where('role', 'franchisor')
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count();
            }

            // Calculate franchisees chart data for last 7 days
            $franchiseesChartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $franchiseesChartData[] = User::where('role', 'franchisee')
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count();
            }

            // Calculate sales users chart data for last 7 days
            $salesChartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $salesChartData[] = User::where('role', 'sales')
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count();
            }

            $stats = [
                [
                    'title' => 'All registered users',
                    'color' => 'primary',
                    'icon' => 'tabler-users',
                    'stats' => number_format($totalUsers),
                    'height' => 90,
                    'series' => [
                        [
                            'data' => $chartData,
                        ],
                    ],
                    'chartOptions' => [
                        'chart' => [
                            'height' => 90,
                            'type' => 'area',
                            'toolbar' => [
                                'show' => false,
                            ],
                            'sparkline' => [
                                'enabled' => true,
                            ],
                        ],
                        'tooltip' => [
                            'enabled' => false,
                        ],
                        'markers' => [
                            'colors' => 'transparent',
                            'strokeColors' => 'transparent',
                        ],
                        'grid' => [
                            'show' => false,
                        ],
                        'colors' => [$this->getPrimaryColor()],
                        'fill' => [
                            'type' => 'gradient',
                            'gradient' => [
                                'shadeIntensity' => 0.8,
                                'opacityFrom' => 0.6,
                                'opacityTo' => 0.1,
                            ],
                        ],
                        'dataLabels' => [
                            'enabled' => false,
                        ],
                        'stroke' => [
                            'width' => 2,
                            'curve' => 'smooth',
                        ],
                        'xaxis' => [
                            'show' => true,
                            'lines' => [
                                'show' => false,
                            ],
                            'labels' => [
                                'show' => false,
                            ],
                            'stroke' => [
                                'width' => 0,
                            ],
                            'axisBorder' => [
                                'show' => false,
                            ],
                        ],
                        'yaxis' => [
                            'stroke' => [
                                'width' => 0,
                            ],
                            'show' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Active franchisors',
                    'color' => 'success',
                    'icon' => 'tabler-building-store',
                    'stats' => number_format($franchisors),
                    'height' => 90,
                    'series' => [
                        [
                            'data' => $franchisorsChartData,
                        ],
                    ],
                    'chartOptions' => [
                        'chart' => [
                            'height' => 90,
                            'type' => 'area',
                            'toolbar' => [
                                'show' => false,
                            ],
                            'sparkline' => [
                                'enabled' => true,
                            ],
                        ],
                        'tooltip' => [
                            'enabled' => false,
                        ],
                        'markers' => [
                            'colors' => 'transparent',
                            'strokeColors' => 'transparent',
                        ],
                        'grid' => [
                            'show' => false,
                        ],
                        'colors' => [$this->getSuccessColor()],
                        'fill' => [
                            'type' => 'gradient',
                            'gradient' => [
                                'shadeIntensity' => 0.8,
                                'opacityFrom' => 0.6,
                                'opacityTo' => 0.1,
                            ],
                        ],
                        'dataLabels' => [
                            'enabled' => false,
                        ],
                        'stroke' => [
                            'width' => 2,
                            'curve' => 'smooth',
                        ],
                        'xaxis' => [
                            'show' => true,
                            'lines' => [
                                'show' => false,
                            ],
                            'labels' => [
                                'show' => false,
                            ],
                            'stroke' => [
                                'width' => 0,
                            ],
                            'axisBorder' => [
                                'show' => false,
                            ],
                        ],
                        'yaxis' => [
                            'stroke' => [
                                'width' => 0,
                            ],
                            'show' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Active franchisees',
                    'color' => 'info',
                    'icon' => 'tabler-user-check',
                    'stats' => number_format($franchisees),
                    'height' => 90,
                    'series' => [
                        [
                            'data' => $franchiseesChartData,
                        ],
                    ],
                    'chartOptions' => [
                        'chart' => [
                            'height' => 90,
                            'type' => 'area',
                            'toolbar' => [
                                'show' => false,
                            ],
                            'sparkline' => [
                                'enabled' => true,
                            ],
                        ],
                        'tooltip' => [
                            'enabled' => false,
                        ],
                        'markers' => [
                            'colors' => 'transparent',
                            'strokeColors' => 'transparent',
                        ],
                        'grid' => [
                            'show' => false,
                        ],
                        'colors' => [$this->getInfoColor()],
                        'fill' => [
                            'type' => 'gradient',
                            'gradient' => [
                                'shadeIntensity' => 0.8,
                                'opacityFrom' => 0.6,
                                'opacityTo' => 0.1,
                            ],
                        ],
                        'dataLabels' => [
                            'enabled' => false,
                        ],
                        'stroke' => [
                            'width' => 2,
                            'curve' => 'smooth',
                        ],
                        'xaxis' => [
                            'show' => true,
                            'lines' => [
                                'show' => false,
                            ],
                            'labels' => [
                                'show' => false,
                            ],
                            'stroke' => [
                                'width' => 0,
                            ],
                            'axisBorder' => [
                                'show' => false,
                            ],
                        ],
                        'yaxis' => [
                            'stroke' => [
                                'width' => 0,
                            ],
                            'show' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Sales team members',
                    'color' => 'warning',
                    'icon' => 'tabler-chart-line',
                    'stats' => number_format($salesUsers),
                    'height' => 90,
                    'series' => [
                        [
                            'data' => $salesChartData,
                        ],
                    ],
                    'chartOptions' => [
                        'chart' => [
                            'height' => 90,
                            'type' => 'area',
                            'toolbar' => [
                                'show' => false,
                            ],
                            'sparkline' => [
                                'enabled' => true,
                            ],
                        ],
                        'tooltip' => [
                            'enabled' => false,
                        ],
                        'markers' => [
                            'colors' => 'transparent',
                            'strokeColors' => 'transparent',
                        ],
                        'grid' => [
                            'show' => false,
                        ],
                        'colors' => [$this->getWarningColor()],
                        'fill' => [
                            'type' => 'gradient',
                            'gradient' => [
                                'shadeIntensity' => 0.8,
                                'opacityFrom' => 0.6,
                                'opacityTo' => 0.1,
                            ],
                        ],
                        'dataLabels' => [
                            'enabled' => false,
                        ],
                        'stroke' => [
                            'width' => 2,
                            'curve' => 'smooth',
                        ],
                        'xaxis' => [
                            'show' => true,
                            'lines' => [
                                'show' => false,
                            ],
                            'labels' => [
                                'show' => false,
                            ],
                            'stroke' => [
                                'width' => 0,
                            ],
                            'axisBorder' => [
                                'show' => false,
                            ],
                        ],
                        'yaxis' => [
                            'stroke' => [
                                'width' => 0,
                            ],
                            'show' => false,
                        ],
                    ],
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage(),
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
                'data' => $recentUsers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent users',
                'error' => $e->getMessage(),
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
                    'users' => $count,
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
                    'revenue' => $revenue,
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
                    'requests' => $requests,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $userChartData,
                    'revenue' => $revenueChartData,
                    'requests' => $requestsChartData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chart data',
                'error' => $e->getMessage(),
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

            return response()->json([
                'success' => true,
                'data' => $franchisors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchisors',
                'error' => $e->getMessage(),
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

            return response()->json([
                'success' => true,
                'data' => $franchisees,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchisees',
                'error' => $e->getMessage(),
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

            return response()->json([
                'success' => true,
                'data' => $salesUsers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales users',
                'error' => $e->getMessage(),
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
                    'errors' => $validator->errors(),
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
                    'business_name' => $request->franchiseName,
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
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage(),
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
                    'errors' => $validator->errors(),
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
                    'business_name' => $request->franchiseName,
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
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage(),
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
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage(),
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
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password',
                'error' => $e->getMessage(),
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

            return response()->json([
                'success' => true,
                'data' => $requests,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch technical requests',
                'error' => $e->getMessage(),
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
                    'errors' => $validator->errors(),
                ], 422);
            }

            $technicalRequest->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Technical request status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update technical request status',
                'error' => $e->getMessage(),
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
                'message' => 'Technical request deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete technical request',
                'error' => $e->getMessage(),
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

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchisor stats',
                'error' => $e->getMessage(),
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

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchisee stats',
                'error' => $e->getMessage(),
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

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales stats',
                'error' => $e->getMessage(),
            ], 500);
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
                'country' => 'required|string|max:100',
                'size_sqft' => 'nullable|numeric|min:0',
                'monthly_rent' => 'nullable|numeric|min:0',
                'opening_date' => 'nullable|date',
                'status' => 'required|in:planning,construction,training,active,temporarily_closed,permanently_closed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
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
                'country' => $request->country,
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

            return response()->json([
                'success' => true,
                'message' => 'Franchisee and unit created successfully',
                'data' => [
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
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create franchisee and unit',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
