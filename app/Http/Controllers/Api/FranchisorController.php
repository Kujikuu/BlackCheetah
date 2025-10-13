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
                ->where('period_year', $currentMonth->year)
                ->where('period_month', $currentMonth->month)
                ->sum('amount');

            $previousMonthRevenue = Revenue::whereHas('unit.franchise', function ($query) use ($franchise) {
                $query->where('franchisor_id', $franchise->id);
            })
                ->where('period_year', $previousMonth->year)
                ->where('period_month', $previousMonth->month)
                ->sum('amount');

            $revenueChange = $previousMonthRevenue > 0
                ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
                : 0;

            // Pending royalties
            $pendingRoyalties = Royalty::whereHas('unit.franchise', function ($query) use ($franchise) {
                $query->where('franchisor_id', $franchise->id);
            })
                ->where('status', 'pending')
                ->sum('total_amount');

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
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

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
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

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
                        'id' => $lead->id,
                        'title' => "New lead: {$lead->first_name} {$lead->last_name}",
                        'description' => "Lead from {$lead->lead_source}",
                        'week' => 'Week '.Carbon::parse($lead->created_at)->weekOfYear,
                        'date' => Carbon::parse($lead->created_at)->format('M d, Y'),
                        'status' => $lead->status === 'new' ? 'scheduled' : ($lead->status === 'converted' ? 'completed' : $lead->status),
                        'icon' => 'tabler-user-plus',
                        'created_at' => $lead->created_at->toISOString(),
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
                        'id' => $task->id,
                        'title' => "Task assigned: {$task->title}",
                        'description' => $task->description,
                        'week' => 'Week '.Carbon::parse($task->created_at)->weekOfYear,
                        'date' => Carbon::parse($task->created_at)->format('M d, Y'),
                        'status' => $task->status === 'pending' ? 'scheduled' : $task->status,
                        'icon' => 'tabler-checklist',
                        'created_at' => $task->created_at->toISOString(),
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
                        'id' => $request->id,
                        'title' => "Technical request: {$request->title}",
                        'description' => $request->description,
                        'week' => 'Week '.Carbon::parse($request->created_at)->weekOfYear,
                        'date' => Carbon::parse($request->created_at)->format('M d, Y'),
                        'status' => $request->status === 'pending' ? 'scheduled' : $request->status,
                        'icon' => 'tabler-tool',
                        'created_at' => $request->created_at->toISOString(),
                    ];
                });

            // Merge and sort all activities
            $timeline = $recentLeads
                ->concat($recentTasks)
                ->concat($recentRequests)
                ->sortByDesc('created_at')
                ->take(20)
                ->values();

            // Calculate stats
            $totalMilestones = $timeline->count();
            $completed = $timeline->where('status', 'completed')->count();
            $scheduled = $timeline->where('status', 'scheduled')->count();
            $overdue = $timeline->where('status', 'overdue')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_milestones' => $totalMilestones,
                        'completed' => $completed,
                        'scheduled' => $scheduled,
                        'overdue' => $overdue,
                    ],
                    'timeline' => $timeline,
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
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

            // Pagination
            $perPage = $request->get('perPage', 10);
            $franchisees = $query->paginate($perPage);

            // Transform the data to include a proper name field
            $franchisees->getCollection()->transform(function ($franchisee) {
                return [
                    'id' => $franchisee->id,
                    'name' => $franchisee->name,
                    'email' => $franchisee->email,
                    'role' => $franchisee->role,
                    'status' => $franchisee->status ?? 'active',
                    'avatar' => $franchisee->avatar,
                    'phone' => $franchisee->phone,
                    'city' => $franchisee->city,
                    'country' => $franchisee->country,
                    'created_at' => $franchisee->created_at,
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

            $query = Unit::whereHas('franchise', function ($q) use ($user) {
                $q->where('franchisor_id', $user->id);
            })->with(['franchisee', 'franchise']);

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
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

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
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

            // Pagination
            $perPage = $request->get('perPage', 10);
            $salesAssociates = $query->paginate($perPage);

            // Transform the data to include assignedLeads count
            $salesAssociates->getCollection()->transform(function ($associate) {
                $name = $associate->name;

                return [
                    'id' => $associate->id,
                    'name' => $name,
                    'email' => $associate->email,
                    'phone' => $associate->phone,
                    'status' => $associate->status ?? 'active',
                    'country' => $associate->country,
                    'city' => $associate->city,
                    'assignedLeads' => $associate->leads_count,
                    'avatar' => $associate->avatar,
                    'avatarText' => $name ? strtoupper(substr($name, 0, 2)) : 'SA',
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

    /**
     * Check franchise profile completion status
     */
    public function profileCompletionStatus(): JsonResponse
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

            // Define required fields for profile completion
            $requiredFields = [
                'business_name',
                'brand_name',
                'industry',
                'description',
                'website',
                'business_registration_number',
                'tax_id',
                'business_type',
                'established_date',
                'headquarters_country',
                'headquarters_city',
                'headquarters_address',
                'contact_phone',
                'contact_email',
                'franchise_fee',
                'royalty_percentage',
            ];

            $missingFields = [];
            $completedFields = 0;

            foreach ($requiredFields as $field) {
                if (empty($franchise->$field)) {
                    $missingFields[] = $field;
                } else {
                    $completedFields++;
                }
            }

            $totalFields = count($requiredFields);
            $completionPercentage = ($completedFields / $totalFields) * 100;
            $isComplete = empty($missingFields);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_complete' => $isComplete,
                    'completion_percentage' => round($completionPercentage, 2),
                    'completed_fields' => $completedFields,
                    'total_fields' => $totalFields,
                    'missing_fields' => $missingFields,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check profile completion status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register a new franchise
     */
    public function registerFranchise(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user already has a franchise
            $existingFranchise = Franchise::where('franchisor_id', $user->id)->first();
            if ($existingFranchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has a franchise registered',
                ], 400);
            }

            // Validate the request data
            $validatedData = $request->validate([
                // Personal Info
                'personalInfo.contactNumber' => 'required|string|max:20',
                'personalInfo.country' => 'required|string|max:100',
                'personalInfo.state' => 'required|string|max:100',
                'personalInfo.city' => 'required|string|max:100',
                'personalInfo.address' => 'required|string|max:500',

                // Franchise Details
                'franchiseDetails.franchiseDetails.franchiseName' => 'required|string|max:255',
                'franchiseDetails.franchiseDetails.website' => 'nullable|url|max:255',
                'franchiseDetails.franchiseDetails.logo' => 'nullable|string',

                // Legal Details
                'franchiseDetails.legalDetails.legalEntityName' => 'required|string|max:255',
                'franchiseDetails.legalDetails.businessStructure' => 'required|in:corporation,llc,partnership,sole_proprietorship',
                'franchiseDetails.legalDetails.taxId' => 'nullable|string|max:50',
                'franchiseDetails.legalDetails.industry' => 'required|string|max:100',
                'franchiseDetails.legalDetails.fundingAmount' => 'nullable|string|max:100',
                'franchiseDetails.legalDetails.fundingSource' => 'nullable|string|max:100',

                // Contact Details
                'franchiseDetails.contactDetails.contactNumber' => 'required|string|max:20',
                'franchiseDetails.contactDetails.email' => 'required|email|max:255',
                'franchiseDetails.contactDetails.address' => 'required|string|max:500',
                'franchiseDetails.contactDetails.country' => 'required|string|max:100',
                'franchiseDetails.contactDetails.state' => 'required|string|max:100',
                'franchiseDetails.contactDetails.city' => 'required|string|max:100',

                // Documents (optional for now)
                'documents.fdd' => 'nullable|string',
                'documents.franchiseAgreement' => 'nullable|string',
                'documents.operationsManual' => 'nullable|string',
                'documents.brandGuidelines' => 'nullable|string',
                'documents.legalDocuments' => 'nullable|string',

                // Review Complete
                'reviewComplete.termsAccepted' => 'required|boolean|accepted',
            ]);

            // Create the franchise record
            $franchise = Franchise::create([
                'franchisor_id' => $user->id,
                'business_name' => $validatedData['franchiseDetails']['franchiseDetails']['franchiseName'],
                'brand_name' => $validatedData['franchiseDetails']['franchiseDetails']['franchiseName'],
                'industry' => $validatedData['franchiseDetails']['legalDetails']['industry'],
                'description' => null, // Will be filled later
                'website' => $validatedData['franchiseDetails']['franchiseDetails']['website'],
                'logo' => $validatedData['franchiseDetails']['franchiseDetails']['logo'],
                'business_registration_number' => 'BRN-'.strtoupper(uniqid()).'-'.$user->id, // Generate unique business registration number
                'tax_id' => $validatedData['franchiseDetails']['legalDetails']['taxId'],
                'business_type' => $validatedData['franchiseDetails']['legalDetails']['businessStructure'],
                'established_date' => null, // Will be filled later
                'headquarters_country' => $validatedData['franchiseDetails']['contactDetails']['country'],
                'headquarters_city' => $validatedData['franchiseDetails']['contactDetails']['city'],
                'headquarters_address' => $validatedData['franchiseDetails']['contactDetails']['address'],
                'contact_phone' => $validatedData['franchiseDetails']['contactDetails']['contactNumber'],
                'contact_email' => $validatedData['franchiseDetails']['contactDetails']['email'],
                'franchise_fee' => null, // Will be filled later
                'royalty_percentage' => null, // Will be filled later
                'marketing_fee_percentage' => null,
                'total_units' => 0,
                'active_units' => 0,
                'status' => 'pending_approval', // Start as pending until approved
                'plan' => 'Basic',
                'business_hours' => null,
                'social_media' => null,
                'documents' => [
                    'fdd' => $validatedData['documents']['fdd'] ?? null,
                    'franchise_agreement' => $validatedData['documents']['franchiseAgreement'] ?? null,
                    'operations_manual' => $validatedData['documents']['operationsManual'] ?? null,
                    'brand_guidelines' => $validatedData['documents']['brandGuidelines'] ?? null,
                    'legal_documents' => $validatedData['documents']['legalDocuments'] ?? null,
                    'funding_amount' => $validatedData['franchiseDetails']['legalDetails']['fundingAmount'] ?? null,
                    'funding_source' => $validatedData['franchiseDetails']['legalDetails']['fundingSource'] ?? null,
                ],
            ]);

            // Update user's contact information
            $user->update([
                'phone' => $validatedData['personalInfo']['contactNumber'],
                'address' => $validatedData['personalInfo']['address'],
                'city' => $validatedData['personalInfo']['city'],
                'state' => $validatedData['personalInfo']['state'],
                'country' => $validatedData['personalInfo']['country'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Franchise registered successfully',
                'data' => [
                    'franchise_id' => $franchise->id,
                    'status' => $franchise->status,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register franchise',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get franchise data for the authenticated franchisor
     */
    public function getFranchiseData(): JsonResponse
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

            // Get documents from the documents JSON field
            $documents = $franchise->documents ?? [];

            // Format the response to match the frontend structure
            $franchiseData = [
                'id' => $franchise->id,
                'franchiseDetails' => [
                    'franchiseName' => $franchise->brand_name,
                    'website' => $franchise->website,
                    'logo' => $franchise->logo,
                ],
                'legalDetails' => [
                    'legalEntityName' => $franchise->business_name,
                    'businessStructure' => ucfirst($franchise->business_type),
                    'taxId' => $franchise->tax_id,
                    'industry' => $franchise->industry,
                    'fundingAmount' => $documents['funding_amount'] ?? null,
                    'fundingSource' => $documents['funding_source'] ?? null,
                ],
                'contactDetails' => [
                    'contactNumber' => $franchise->contact_phone,
                    'email' => $franchise->contact_email,
                    'address' => $franchise->headquarters_address,
                    'country' => $franchise->headquarters_country,
                    'state' => $user->state,
                    'city' => $franchise->headquarters_city,
                ],
            ];

            // Get documents data
            $documentsData = [];
            $documentTypes = [
                'fdd' => 'Franchise Disclosure Document',
                'franchise_agreement' => 'Franchise Agreement',
                'operations_manual' => 'Operations Manual',
                'brand_guidelines' => 'Brand Guidelines',
                'legal_documents' => 'Legal Documents',
            ];

            $id = 1;
            foreach ($documentTypes as $key => $title) {
                if (! empty($documents[$key])) {
                    $documentsData[] = [
                        'id' => $id++,
                        'title' => $title,
                        'description' => "Official {$title} for {$franchise->brand_name}",
                        'fileName' => "{$key}.pdf",
                        'fileSize' => '2.4 MB', // Mock size for now
                        'uploadDate' => $franchise->created_at->format('Y-m-d'),
                        'type' => ucfirst(str_replace('_', ' ', $key)),
                    ];
                }
            }

            // Get products data (mock for now since we don't have a products table)
            $productsData = [];

            return response()->json([
                'success' => true,
                'data' => [
                    'franchise' => $franchiseData,
                    'documents' => $documentsData,
                    'products' => $productsData,
                    'stats' => [
                        'status' => ucfirst($franchise->status),
                        'totalDocuments' => count($documentsData),
                        'totalProducts' => count($productsData),
                        'activeProducts' => count($productsData),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch franchise data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update franchise data
     */
    public function updateFranchise(Request $request): JsonResponse
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

            // Validate the request data
            $validatedData = $request->validate([
                // Personal Info
                'personalInfo.contactNumber' => 'sometimes|nullable|string|max:20',
                'personalInfo.country' => 'sometimes|nullable|string|max:100',
                'personalInfo.state' => 'sometimes|nullable|string|max:100',
                'personalInfo.city' => 'sometimes|nullable|string|max:100',
                'personalInfo.address' => 'sometimes|nullable|string|max:500',

                // Franchise Details
                'franchiseDetails.franchiseDetails.franchiseName' => 'sometimes|nullable|string|max:255',
                'franchiseDetails.franchiseDetails.website' => 'sometimes|nullable|url|max:255',
                'franchiseDetails.franchiseDetails.logo' => 'sometimes|nullable|string',

                // Legal Details
                'franchiseDetails.legalDetails.legalEntityName' => 'sometimes|nullable|string|max:255',
                'franchiseDetails.legalDetails.businessStructure' => 'sometimes|nullable|in:corporation,llc,partnership,sole_proprietorship',
                'franchiseDetails.legalDetails.taxId' => 'sometimes|nullable|string|max:50',
                'franchiseDetails.legalDetails.industry' => 'sometimes|nullable|string|max:100',
                'franchiseDetails.legalDetails.fundingAmount' => 'sometimes|nullable|string|max:100',
                'franchiseDetails.legalDetails.fundingSource' => 'sometimes|nullable|string|max:100',

                // Contact Details
                'franchiseDetails.contactDetails.contactNumber' => 'sometimes|nullable|string|max:20',
                'franchiseDetails.contactDetails.email' => 'sometimes|nullable|email|max:255',
                'franchiseDetails.contactDetails.address' => 'sometimes|nullable|string|max:500',
                'franchiseDetails.contactDetails.country' => 'sometimes|nullable|string|max:100',
                'franchiseDetails.contactDetails.state' => 'sometimes|nullable|string|max:100',
                'franchiseDetails.contactDetails.city' => 'sometimes|nullable|string|max:100',
            ]);

            // Update franchise fields
            $updateData = [];

            // Use array_key_exists to check for null values as well
            if (array_key_exists('franchiseDetails', $validatedData)) {
                if (array_key_exists('franchiseDetails', $validatedData['franchiseDetails'])) {
                    if (array_key_exists('franchiseName', $validatedData['franchiseDetails']['franchiseDetails'])) {
                        $updateData['brand_name'] = $validatedData['franchiseDetails']['franchiseDetails']['franchiseName'];
                    }
                    if (array_key_exists('website', $validatedData['franchiseDetails']['franchiseDetails'])) {
                        $updateData['website'] = $validatedData['franchiseDetails']['franchiseDetails']['website'];
                    }
                    if (array_key_exists('logo', $validatedData['franchiseDetails']['franchiseDetails'])) {
                        $updateData['logo'] = $validatedData['franchiseDetails']['franchiseDetails']['logo'];
                    }
                }

                if (array_key_exists('legalDetails', $validatedData['franchiseDetails'])) {
                    if (array_key_exists('legalEntityName', $validatedData['franchiseDetails']['legalDetails'])) {
                        $updateData['business_name'] = $validatedData['franchiseDetails']['legalDetails']['legalEntityName'];
                    }
                    if (array_key_exists('businessStructure', $validatedData['franchiseDetails']['legalDetails'])) {
                        $updateData['business_type'] = $validatedData['franchiseDetails']['legalDetails']['businessStructure'];
                    }
                    if (array_key_exists('taxId', $validatedData['franchiseDetails']['legalDetails'])) {
                        $updateData['tax_id'] = $validatedData['franchiseDetails']['legalDetails']['taxId'];
                    }
                    if (array_key_exists('industry', $validatedData['franchiseDetails']['legalDetails'])) {
                        $updateData['industry'] = $validatedData['franchiseDetails']['legalDetails']['industry'];
                    }
                }

                if (array_key_exists('contactDetails', $validatedData['franchiseDetails'])) {
                    if (array_key_exists('contactNumber', $validatedData['franchiseDetails']['contactDetails'])) {
                        $updateData['contact_phone'] = $validatedData['franchiseDetails']['contactDetails']['contactNumber'];
                    }
                    if (array_key_exists('email', $validatedData['franchiseDetails']['contactDetails'])) {
                        $updateData['contact_email'] = $validatedData['franchiseDetails']['contactDetails']['email'];
                    }
                    if (array_key_exists('address', $validatedData['franchiseDetails']['contactDetails'])) {
                        $updateData['headquarters_address'] = $validatedData['franchiseDetails']['contactDetails']['address'];
                    }
                    if (array_key_exists('country', $validatedData['franchiseDetails']['contactDetails'])) {
                        $updateData['headquarters_country'] = $validatedData['franchiseDetails']['contactDetails']['country'];
                    }
                    if (array_key_exists('city', $validatedData['franchiseDetails']['contactDetails'])) {
                        $updateData['headquarters_city'] = $validatedData['franchiseDetails']['contactDetails']['city'];
                    }
                }
            }

            // Update documents if funding info is provided
            if (
                array_key_exists('fundingAmount', $validatedData['franchiseDetails']['legalDetails'] ?? []) ||
                array_key_exists('fundingSource', $validatedData['franchiseDetails']['legalDetails'] ?? [])
            ) {
                $documents = $franchise->documents ?? [];
                if (array_key_exists('fundingAmount', $validatedData['franchiseDetails']['legalDetails'] ?? [])) {
                    $documents['funding_amount'] = $validatedData['franchiseDetails']['legalDetails']['fundingAmount'];
                }
                if (array_key_exists('fundingSource', $validatedData['franchiseDetails']['legalDetails'] ?? [])) {
                    $documents['funding_source'] = $validatedData['franchiseDetails']['legalDetails']['fundingSource'];
                }
                $updateData['documents'] = $documents;
            }

            // Update franchise
            $franchise->update($updateData);

            // Update user's contact information if provided
            $userUpdateData = [];
            if (array_key_exists('personalInfo', $validatedData)) {
                if (array_key_exists('contactNumber', $validatedData['personalInfo'])) {
                    $userUpdateData['phone'] = $validatedData['personalInfo']['contactNumber'];
                }
                if (array_key_exists('address', $validatedData['personalInfo'])) {
                    $userUpdateData['address'] = $validatedData['personalInfo']['address'];
                }
                if (array_key_exists('city', $validatedData['personalInfo'])) {
                    $userUpdateData['city'] = $validatedData['personalInfo']['city'];
                }
                if (array_key_exists('state', $validatedData['personalInfo'])) {
                    $userUpdateData['state'] = $validatedData['personalInfo']['state'];
                }
                if (array_key_exists('country', $validatedData['personalInfo'])) {
                    $userUpdateData['country'] = $validatedData['personalInfo']['country'];
                }
            }

            if (! empty($userUpdateData)) {
                $user->update($userUpdateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Franchise updated successfully',
                'data' => [
                    'franchise_id' => $franchise->id,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update franchise',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get units statistics for the franchisor
     */
    public function unitsStatistics(): JsonResponse
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

            // Get all units for this franchisor
            $units = Unit::whereHas('franchise', function ($query) use ($user) {
                $query->where('franchisor_id', $user->id);
            })->get();

            // Calculate basic statistics
            $totalUnits = $units->count();
            $activeUnits = $units->where('status', 'active')->count();
            $pendingUnits = $units->where('status', 'planning')->count();
            $inactiveUnits = $units->where('status', 'inactive')->count();

            // Calculate revenue statistics
            $totalRevenue = 0;
            $monthlyRoyalty = 0;
            $royaltyRate = 8.5; // Default royalty rate - could come from franchise settings

            // If franchise has a specific royalty rate, use that
            if ($franchise->royalty_percentage) {
                $royaltyRate = $franchise->royalty_percentage;
            }

            foreach ($units as $unit) {
                // Mock revenue calculation - in real app, this would come from revenue table
                $unitRevenue = rand(50000, 200000); // Mock monthly revenue per unit
                $totalRevenue += $unitRevenue;
                $monthlyRoyalty += ($unitRevenue * $royaltyRate) / 100;
            }

            // Calculate task statistics
            $totalTasks = Task::where('created_by', $user->id)->count();
            $completedTasks = Task::where('created_by', $user->id)
                ->where('status', 'completed')
                ->count();
            $pendingTasks = Task::where('created_by', $user->id)
                ->where('status', 'pending')
                ->count();

            // Calculate performance metrics
            $avgRevenuePerUnit = $totalUnits > 0 ? $totalRevenue / $totalUnits : 0;
            $taskCompletionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

            // Previous month statistics for comparison
            $previousMonthRevenue = $totalRevenue * 0.9; // Mock 10% decrease from previous month
            $revenueChange = $previousMonthRevenue > 0
                ? (($totalRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'totalUnits' => $totalUnits,
                    'activeUnits' => $activeUnits,
                    'pendingUnits' => $pendingUnits,
                    'inactiveUnits' => $inactiveUnits,
                    'totalRevenue' => $totalRevenue,
                    'monthlyRoyalty' => $monthlyRoyalty,
                    'avgRevenuePerUnit' => $avgRevenuePerUnit,
                    'revenueChange' => round($revenueChange, 2),
                    'royaltyRate' => $royaltyRate,
                    'taskStats' => [
                        'total' => $totalTasks,
                        'completed' => $completedTasks,
                        'pending' => $pendingTasks,
                        'completionRate' => round($taskCompletionRate, 2),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch units statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload franchise logo
     */
    public function uploadLogo(Request $request): JsonResponse
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
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Store the logo file
            $logoPath = $request->file('logo')->store('franchise-logos', 'public');
            $logoUrl = asset('storage/'.$logoPath);

            // Update franchise with new logo URL
            $franchise->update(['logo' => $logoUrl]);

            return response()->json([
                'success' => true,
                'message' => 'Logo uploaded successfully',
                'data' => [
                    'logo_url' => $logoUrl,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload logo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a franchisee with associated unit for the authenticated franchisor
     */
    public function createFranchiseeWithUnit(Request $request): JsonResponse
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

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                // Franchisee details
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',

                // Unit details
                'unit_name' => 'required|string|max:255',
                'unit_type' => 'required|in:store,kiosk,mobile,online,warehouse,office',
                'address' => 'required|string',
                'city' => 'required|string|max:100',
                'state_province' => 'required|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'required|string|max:100',
                'size_sqft' => 'nullable|numeric|min:0',
                'monthly_rent' => 'nullable|numeric|min:0',
                'opening_date' => 'nullable|date',
                'status' => 'nullable|in:planning,construction,training,active,temporarily_closed,permanently_closed',
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
            $temporaryPassword = \Illuminate\Support\Str::random(12);

            // Create franchisee user
            $franchisee = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($temporaryPassword),
                'role' => 'franchisee',
                'status' => 'active',
                'phone' => $request->phone,
                'city' => $request->city,
                'profile_completed' => false, // Require onboarding
            ]);

            // Generate unit code
            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $franchise->name), 0, 3));
            if (empty($prefix)) {
                $prefix = 'UNI';
            }

            $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $request->unit_name), 0, 6));
            if (empty($baseCode)) {
                $baseCode = 'UNIT';
            }

            $unitCode = $prefix.'-'.$baseCode;
            $counter = 1;

            // Ensure uniqueness
            while (Unit::where('unit_code', $unitCode)->exists()) {
                $unitCode = $prefix.'-'.$baseCode.$counter;
                $counter++;
            }

            // Create associated unit with franchisee as manager
            $unit = Unit::create([
                'unit_name' => $request->unit_name,
                'unit_code' => $unitCode,
                'franchise_id' => $franchise->id, // Use authenticated franchisor's franchise
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
                'status' => $request->status ?? 'planning',
                'employee_count' => 0,
            ]);

            // Send email notification with login credentials
            $loginUrl = env('APP_URL').'/login';
            $franchisee->notify(new \App\Notifications\NewFranchiseeCredentials($temporaryPassword, $unitCode, $loginUrl));

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

    /**
     * Get staff members for a specific unit
     */
    public function getUnitStaff(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();

        // Get the unit and verify it belongs to the franchisor's franchise
        $unit = Unit::whereHas('franchise', function ($query) use ($user) {
            $query->where('franchisor_id', $user->id);
        })->find($unitId);

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        // Get active staff members for this unit
        $staffMembers = $unit->activeStaff()
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'phone' => $staff->phone,
                    'jobTitle' => $staff->job_title,
                    'department' => $staff->department,
                    'salary' => $staff->salary,
                    'hireDate' => $staff->hire_date?->format('Y-m-d'),
                    'shiftStart' => $staff->shift_start?->format('H:i'),
                    'shiftEnd' => $staff->shift_end?->format('H:i'),
                    'shiftTime' => $staff->full_shift_time,
                    'status' => $staff->status === 'active' ? 'working' : ($staff->status === 'on_leave' ? 'leave' : $staff->status),
                    'employmentType' => $staff->employment_type,
                    'notes' => $staff->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $staffMembers,
            'message' => 'Unit staff retrieved successfully',
        ]);
    }
}
