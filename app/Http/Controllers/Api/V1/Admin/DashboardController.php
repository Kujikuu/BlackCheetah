<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Revenue;
use App\Models\TechnicalRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseAdminController
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

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch dashboard statistics', $e->getMessage(), 500);
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

            return $this->successResponse($recentUsers);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch recent users', $e->getMessage(), 500);
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

            return $this->successResponse([
                'users' => $userChartData,
                'revenue' => $revenueChartData,
                'requests' => $requestsChartData,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch chart data', $e->getMessage(), 500);
        }
    }
}
