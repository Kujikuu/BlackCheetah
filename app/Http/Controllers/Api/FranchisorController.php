<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Lead;
use App\Models\Revenue;
use App\Models\Royalty;
use App\Models\Task;
use App\Models\TechnicalRequest;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FranchisorController extends Controller
{
    /**
     * Get franchisor dashboard statistics
     */
    public function dashboardStats(): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            // Get current month and previous month for comparison
            $currentMonth = Carbon::now()->startOfMonth();
            $previousMonth = Carbon::now()->subMonth()->startOfMonth();

            // Total franchisees under this franchisor
            $totalFranchisees = User::where('role', 'franchisee')
                ->whereHas('franchise', function ($query) use ($franchise) {
                    $query->where('franchisor_id', $franchise->id);
                })
                ->count();

            // Total units under this franchisor
            $totalUnits = Unit::whereHas('franchise', function ($query) use ($franchise) {
                $query->where('franchisor_id', $franchise->id);
            })->count();

            // Total leads for this franchisor
            $totalLeads = Lead::where('franchise_id', $franchise->id)->count();

            // Active tasks assigned by this franchisor
            $activeTasks = Task::where('created_by', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            // Revenue statistics
            $currentMonthRevenue = Revenue::whereHas('unit.franchise', function ($query) use ($franchise) {
                $query->where('franchisor_id', $franchise->id);
            })
                ->where('month', $currentMonth->format('Y-m'))
                ->sum('amount');

            $previousMonthRevenue = Revenue::whereHas('unit.franchise', function ($query) use ($franchise) {
                $query->where('franchisor_id', $franchise->id);
            })
                ->where('month', $previousMonth->format('Y-m'))
                ->sum('amount');

            $revenueChange = $previousMonthRevenue > 0
                ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
                : 0;

            // Pending royalties
            $pendingRoyalties = Royalty::whereHas('unit.franchise', function ($query) use ($franchise) {
                $query->where('franchisor_id', $franchise->id);
            })
                ->where('status', 'pending')
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'totalFranchisees' => $totalFranchisees,
                    'totalUnits' => $totalUnits,
                    'totalLeads' => $totalLeads,
                    'activeTasks' => $activeTasks,
                    'currentMonthRevenue' => $currentMonthRevenue,
                    'revenueChange' => round($revenueChange, 2),
                    'pendingRoyalties' => $pendingRoyalties,
                ],
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
     * Get finance dashboard data
     */
    public function financeStats(): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            // Get last 12 months for charts
            $months = collect();
            for ($i = 11; $i >= 0; $i--) {
                $months->push(Carbon::now()->subMonths($i)->format('Y-m'));
            }

            // Revenue by month
            $revenueByMonth = Revenue::where('franchise_id', $franchise->id)
                ->whereIn(DB::raw('CONCAT(period_year, "-", LPAD(period_month, 2, "0"))'), $months)
                ->groupBy(DB::raw('CONCAT(period_year, "-", LPAD(period_month, 2, "0"))'))
                ->selectRaw('CONCAT(period_year, "-", LPAD(period_month, 2, "0")) as month, SUM(amount) as total')
                ->pluck('total', 'month');

            // Royalties by month
            $royaltiesByMonth = Royalty::where('franchise_id', $franchise->id)
                ->where('status', 'paid')
                ->whereIn(DB::raw('CONCAT(period_year, "-", LPAD(period_month, 2, "0"))'), $months)
                ->groupBy(DB::raw('CONCAT(period_year, "-", LPAD(period_month, 2, "0"))'))
                ->selectRaw('CONCAT(period_year, "-", LPAD(period_month, 2, "0")) as month, SUM(total_amount) as total')
                ->pluck('total', 'month');

            // Expenses by month (from transactions)
            $expensesByMonth = Transaction::where('franchise_id', $franchise->id)
                ->where('type', 'expense')
                ->whereIn(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")'), $months)
                ->groupBy(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")'))
                ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month, SUM(amount) as total')
                ->pluck('total', 'month');

            // Format data for charts
            $chartData = $months->map(function ($month) use ($revenueByMonth, $royaltiesByMonth, $expensesByMonth) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                    'revenue' => $revenueByMonth->get($month, 0),
                    'royalties' => $royaltiesByMonth->get($month, 0),
                    'expenses' => $expensesByMonth->get($month, 0),
                ];
            });

            // Current month totals
            $currentMonth = Carbon::now()->format('Y-m');
            $previousMonth = Carbon::now()->subMonth()->format('Y-m');

            $totalSales = $revenueByMonth->get($currentMonth, 0);
            $previousSales = $revenueByMonth->get($previousMonth, 0);
            $salesChange = $previousSales > 0 ? (($totalSales - $previousSales) / $previousSales) * 100 : 0;

            $totalExpenses = $expensesByMonth->get($currentMonth, 0);
            $previousExpenses = $expensesByMonth->get($previousMonth, 0);
            $expensesChange = $previousExpenses > 0 ? (($totalExpenses - $previousExpenses) / $previousExpenses) * 100 : 0;

            $totalProfit = $totalSales - $totalExpenses;
            $previousProfit = $previousSales - $previousExpenses;
            $profitChange = $previousProfit > 0 ? (($totalProfit - $previousProfit) / $previousProfit) * 100 : 0;

            $profitMargin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;
            $previousMargin = $previousSales > 0 ? ($previousProfit / $previousSales) * 100 : 0;
            $marginChange = $profitMargin - $previousMargin;

            // Top performing units - simplified for now since units might not have direct revenues
            $topUnits = Unit::where('franchise_id', $franchise->id)
                ->limit(5)
                ->get()
                ->map(function ($unit, $index) {
                    return [
                        'name' => $unit->name,
                        'sales' => rand(10000, 50000), // Mock data for now
                        'royalty' => rand(1000, 5000), // Mock data for now
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_sales' => $totalSales,
                        'sales_change' => round($salesChange, 2),
                        'total_expenses' => $totalExpenses,
                        'expenses_change' => round($expensesChange, 2),
                        'net_profit' => $totalProfit,
                        'profit_change' => round($profitChange, 2),
                        'profit_margin' => round($profitMargin, 2),
                        'margin_change' => round($marginChange, 2),
                    ],
                    'top_stores_sales' => $topUnits->toArray(),
                    'top_stores_royalty' => $topUnits->toArray(),
                    'sales_chart' => $chartData->map(function ($item) {
                        return [
                            'month' => $item['month'],
                            'amount' => $item['revenue'],
                        ];
                    })->toArray(),
                    'expenses_chart' => $chartData->map(function ($item) {
                        return [
                            'month' => $item['month'],
                            'amount' => $item['expenses'],
                        ];
                    })->toArray(),
                    'profit_chart' => $chartData->map(function ($item) {
                        return [
                            'month' => $item['month'],
                            'amount' => $item['revenue'] - $item['expenses'],
                        ];
                    })->toArray(),
                    'royalty_chart' => $chartData->map(function ($item) {
                        return [
                            'month' => $item['month'],
                            'amount' => $item['royalties'],
                        ];
                    })->toArray(),
                    'monthly_breakdown' => $chartData->map(function ($item) {
                        return [
                            'month' => $item['month'],
                            'sales' => $item['revenue'],
                            'expenses' => $item['expenses'],
                            'royalties' => $item['royalties'],
                            'profit' => $item['revenue'] - $item['expenses'],
                        ];
                    })->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch finance statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get leads dashboard data
     */
    public function leadsStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $query = Lead::where('franchise_id', $franchise->id);

            // Apply filters
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('source') && $request->source) {
                $query->where('lead_source', $request->source);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sortBy', 'created_at');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('perPage', 10);
            $leads = $query->paginate($perPage);

            // Lead statistics
            $totalLeads = Lead::where('franchise_id', $franchise->id)->count();
            $pendingLeads = Lead::where('franchise_id', $franchise->id)->where('status', 'pending')->count();
            $wonLeads = Lead::where('franchise_id', $franchise->id)->where('status', 'won')->count();
            $lostLeads = Lead::where('franchise_id', $franchise->id)->where('status', 'lost')->count();

            // Previous month statistics for change calculation
            $previousMonth = Carbon::now()->subMonth();
            $prevTotalLeads = Lead::where('franchise_id', $franchise->id)
                ->whereMonth('created_at', $previousMonth->month)
                ->whereYear('created_at', $previousMonth->year)
                ->count();
            $prevPendingLeads = Lead::where('franchise_id', $franchise->id)
                ->where('status', 'pending')
                ->whereMonth('created_at', $previousMonth->month)
                ->whereYear('created_at', $previousMonth->year)
                ->count();
            $prevWonLeads = Lead::where('franchise_id', $franchise->id)
                ->where('status', 'won')
                ->whereMonth('created_at', $previousMonth->month)
                ->whereYear('created_at', $previousMonth->year)
                ->count();
            $prevLostLeads = Lead::where('franchise_id', $franchise->id)
                ->where('status', 'lost')
                ->whereMonth('created_at', $previousMonth->month)
                ->whereYear('created_at', $previousMonth->year)
                ->count();

            // Calculate percentage changes
            $totalLeadsChange = $prevTotalLeads > 0 ? (($totalLeads - $prevTotalLeads) / $prevTotalLeads) * 100 : 0;
            $pendingLeadsChange = $prevPendingLeads > 0 ? (($pendingLeads - $prevPendingLeads) / $prevPendingLeads) * 100 : 0;
            $wonLeadsChange = $prevWonLeads > 0 ? (($wonLeads - $prevWonLeads) / $prevWonLeads) * 100 : 0;
            $lostLeadsChange = $prevLostLeads > 0 ? (($lostLeads - $prevLostLeads) / $prevLostLeads) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_leads' => $totalLeads,
                        'total_leads_change' => round($totalLeadsChange, 2),
                        'pending_leads' => $pendingLeads,
                        'pending_leads_change' => round($pendingLeadsChange, 2),
                        'won_leads' => $wonLeads,
                        'won_leads_change' => round($wonLeadsChange, 2),
                        'lost_leads' => $lostLeads,
                        'lost_leads_change' => round($lostLeadsChange, 2),
                    ],
                    'leads' => $leads->items(),
                    'pagination' => [
                        'total' => $leads->total(),
                        'per_page' => $leads->perPage(),
                        'current_page' => $leads->currentPage(),
                        'last_page' => $leads->lastPage(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leads data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get operations dashboard data
     */
    public function operationsStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $type = $request->get('type', 'franchisee'); // franchisee or unit

            if ($type === 'franchisee') {
                // Tasks assigned to franchisees under this franchisor
                $query = Task::where('created_by', $user->id)
                    ->whereHas('assignedUser', function ($q) {
                        $q->where('role', 'franchisee');
                    });
            } else {
                // Tasks assigned to units under this franchisor
                $query = Task::where('created_by', $user->id)
                    ->whereHas('unit.franchise', function ($q) use ($franchise) {
                        $q->where('franchisor_id', $franchise->id);
                    });
            }

            // Apply filters
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('priority') && $request->priority) {
                $query->where('priority', $request->priority);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sortBy', 'created_at');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('perPage', 10);
            $tasks = $query->with(['assignedUser', 'unit'])->paginate($perPage);

            // Task statistics
            $totalTasks = Task::where('created_by', $user->id)->count();
            $pendingTasks = Task::where('created_by', $user->id)->where('status', 'pending')->count();
            $inProgressTasks = Task::where('created_by', $user->id)->where('status', 'in_progress')->count();
            $completedTasks = Task::where('created_by', $user->id)->where('status', 'completed')->count();

            // Tasks by priority
            $tasksByPriority = Task::where('created_by', $user->id)
                ->groupBy('priority')
                ->selectRaw('priority, COUNT(*) as count')
                ->get()
                ->pluck('count', 'priority');

            return response()->json([
                'success' => true,
                'data' => [
                    'tasks' => $tasks,
                    'statistics' => [
                        'total' => $totalTasks,
                        'pending' => $pendingTasks,
                        'inProgress' => $inProgressTasks,
                        'completed' => $completedTasks,
                    ],
                    'tasksByPriority' => $tasksByPriority,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch operations data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get timeline dashboard data
     */
    public function timelineStats(): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            // Recent activities (last 30 days)
            $activities = collect();

            // Recent leads
            $recentLeads = Lead::where('franchise_id', $franchise->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($lead) {
                    return [
                        'type' => 'lead',
                        'title' => "New lead: {$lead->first_name} {$lead->last_name}",
                        'description' => "Lead from {$lead->lead_source}",
                        'date' => $lead->created_at,
                        'status' => $lead->status,
                    ];
                });

            // Recent tasks
            $recentTasks = Task::where('created_by', $user->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($task) {
                    return [
                        'type' => 'task',
                        'title' => "Task assigned: {$task->title}",
                        'description' => $task->description,
                        'date' => $task->created_at,
                        'status' => $task->status,
                    ];
                });

            // Recent technical requests
            $recentRequests = TechnicalRequest::whereHas('requester.franchise', function ($query) use ($franchise) {
                $query->where('franchisor_id', $franchise->id);
            })
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($request) {
                    return [
                        'type' => 'technical_request',
                        'title' => "Technical request: {$request->title}",
                        'description' => $request->description,
                        'date' => $request->created_at,
                        'status' => $request->status,
                    ];
                });

            // Merge and sort all activities
            $activities = $recentLeads
                ->concat($recentTasks)
                ->concat($recentRequests)
                ->sortByDesc('date')
                ->take(20)
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'activities' => $activities,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch timeline data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get franchisor's franchise information
     */
    public function myFranchise(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Get franchise where this user is the franchisor
            $franchise = Franchise::where('franchisor_id', $user->id)
                ->with(['units', 'franchisor'])
                ->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $franchise,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchise information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get franchisor's franchisees
     */
    public function myFranchisees(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $query = User::where('role', 'franchisee')
                ->whereHas('franchise', function ($q) use ($franchise) {
                    $q->where('franchisor_id', $franchise->id);
                });

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
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

            // Pagination
            $perPage = $request->get('perPage', 10);
            $franchisees = $query->paginate($perPage);

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
     * Get franchisor's units
     */
    public function myUnits(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $query = Unit::whereHas('franchise', function ($q) use ($franchise) {
                $q->where('franchisor_id', $franchise->id);
            })->with(['manager', 'franchise']);

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
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

            // Pagination
            $perPage = $request->get('perPage', 10);
            $units = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $units,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch units',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sales associates for the franchisor
     */
    public function salesAssociatesIndex(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $query = User::where('role', 'sales')
                ->where('franchise_id', $franchise->id)
                ->withCount('leads'); // Get the count of assigned leads

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Role filter removed - sales_role field no longer exists

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sortBy = $request->get('sortBy', 'created_at');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('perPage', 10);
            $salesAssociates = $query->paginate($perPage);

            // Transform the data to include assignedLeads count
            $salesAssociates->getCollection()->transform(function ($associate) {
                return [
                    'id' => $associate->id,
                    'name' => $associate->name,
                    'email' => $associate->email,
                    'phone' => $associate->phone,
                    'status' => $associate->status,
                    'country' => $associate->country,
                    'state' => $associate->state,
                    'city' => $associate->city,
                    'assignedLeads' => $associate->leads_count,
                    'avatar' => $associate->avatar,
                    'avatarText' => $associate->name ? strtoupper(substr($associate->name, 0, 2)) : 'SA',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $salesAssociates->items(),
                'total' => $salesAssociates->total(),
                'per_page' => $salesAssociates->perPage(),
                'current_page' => $salesAssociates->currentPage(),
                'last_page' => $salesAssociates->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales associates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new sales associate
     */
    public function salesAssociatesStore(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:20',
                'status' => 'required|in:active,inactive',
                'country' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'password' => 'required|string|min:8',
            ]);

            $validatedData['role'] = 'sales';
            $validatedData['franchise_id'] = $franchise->id;
            $validatedData['password'] = bcrypt($validatedData['password']);

            $salesAssociate = User::create($validatedData);

            // Load the leads count
            $salesAssociate->loadCount('leads');

            return response()->json([
                'success' => true,
                'message' => 'Sales associate created successfully',
                'data' => [
                    'id' => $salesAssociate->id,
                    'name' => $salesAssociate->name,
                    'email' => $salesAssociate->email,
                    'phone' => $salesAssociate->phone,
                    'status' => $salesAssociate->status,
                    'country' => $salesAssociate->country,
                    'state' => $salesAssociate->state,
                    'city' => $salesAssociate->city,
                    'assignedLeads' => $salesAssociate->leads_count,
                    'avatar' => $salesAssociate->avatar,
                    'avatarText' => strtoupper(substr($salesAssociate->name, 0, 2)),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sales associate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific sales associate
     */
    public function salesAssociatesShow($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $salesAssociate = User::where('role', 'sales')
                ->where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->withCount('leads')
                ->with(['leads' => function ($query) {
                    $query->select('id', 'assigned_to', 'first_name', 'last_name', 'email', 'status', 'priority');
                }])
                ->first();

            if (! $salesAssociate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales associate not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $salesAssociate->id,
                    'name' => $salesAssociate->name,
                    'email' => $salesAssociate->email,
                    'phone' => $salesAssociate->phone,
                    'status' => $salesAssociate->status,
                    'country' => $salesAssociate->country,
                    'state' => $salesAssociate->state,
                    'city' => $salesAssociate->city,
                    'assignedLeads' => $salesAssociate->leads_count,
                    'avatar' => $salesAssociate->avatar,
                    'avatarText' => strtoupper(substr($salesAssociate->name, 0, 2)),
                    'leads' => $salesAssociate->leads,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales associate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a sales associate
     */
    public function salesAssociatesUpdate(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $salesAssociate = User::where('role', 'sales')
                ->where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->first();

            if (! $salesAssociate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales associate not found',
                ], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,'.$id,
                'phone' => 'sometimes|required|string|max:20',
                'status' => 'sometimes|required|in:active,inactive',
                'country' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'password' => 'sometimes|string|min:8',
            ]);

            if (isset($validatedData['password'])) {
                $validatedData['password'] = bcrypt($validatedData['password']);
            }

            $salesAssociate->update($validatedData);

            // Load the leads count
            $salesAssociate->loadCount('leads');

            return response()->json([
                'success' => true,
                'message' => 'Sales associate updated successfully',
                'data' => [
                    'id' => $salesAssociate->id,
                    'name' => $salesAssociate->name,
                    'email' => $salesAssociate->email,
                    'phone' => $salesAssociate->phone,
                    'status' => $salesAssociate->status,
                    'country' => $salesAssociate->country,
                    'state' => $salesAssociate->state,
                    'city' => $salesAssociate->city,
                    'assignedLeads' => $salesAssociate->leads_count,
                    'avatar' => $salesAssociate->avatar,
                    'avatarText' => strtoupper(substr($salesAssociate->name, 0, 2)),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sales associate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a sales associate
     */
    public function salesAssociatesDestroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $salesAssociate = User::where('role', 'sales')
                ->where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->first();

            if (! $salesAssociate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales associate not found',
                ], 404);
            }

            // Unassign leads before deleting the sales associate
            Lead::where('assigned_to', $id)->update(['assigned_to' => null]);

            $salesAssociate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sales associate deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sales associate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
