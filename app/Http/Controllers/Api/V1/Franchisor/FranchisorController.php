<?php

namespace App\Http\Controllers\Api\V1\Franchisor;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Http\Requests\RegisterFranchiseRequest;
use App\Http\Requests\CreateFranchiseeWithUnitRequest;
use App\Http\Requests\UpdateFranchiseRequest;
use App\Http\Requests\StoreBrokerRequest;
use App\Http\Requests\UpdateBrokerRequest;
use App\Models\Franchise;
use App\Models\Lead;
use App\Models\Revenue;
use App\Models\Royalty;
use App\Models\Staff;
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

class FranchisorController extends BaseResourceController
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
                return $this->notFoundResponse('No franchise found for this user');
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

            return $this->successResponse([
                'totalFranchisees' => $totalFranchisees,
                'totalUnits' => $totalUnits,
                'totalLeads' => $totalLeads,
                'activeTasks' => $activeTasks,
                'currentMonthRevenue' => $currentMonthRevenue,
                'revenueChange' => round($revenueChange, 2),
                'pendingRoyalties' => $pendingRoyalties,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch dashboard statistics', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
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

            return $this->successResponse([
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
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch finance statistics', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
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

            return $this->successResponse([
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
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch leads data', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
            }

            // Fetch tasks by role
            $franchiseeTasks = Task::where('franchise_id', $franchise->id)
                ->whereHas('assignedTo', function ($q) {
                    $q->where('role', 'franchisee');
                })
                ->with(['assignedTo', 'createdBy'])
                ->get();

            $brokerTasks = Task::where('franchise_id', $franchise->id)
                ->whereHas('assignedTo', function ($q) {
                    $q->where('role', 'broker');
                })
                ->with(['assignedTo', 'createdBy'])
                ->get();

            // Staff tasks (placeholder for now)
            $staffTasks = collect([]);

            // Calculate statistics for each role
            $stats = [
                'franchisee' => $this->calculateTaskStats($franchiseeTasks),
                'broker' => $this->calculateTaskStats($brokerTasks),
                'staff' => $this->calculateTaskStats($staffTasks),
            ];

            // Transform tasks for frontend
            $tasks = [
                'franchisee' => $this->transformTasks($franchiseeTasks),
                'broker' => $this->transformTasks($brokerTasks),
                'staff' => $this->transformTasks($staffTasks),
            ];

            return $this->successResponse([
                'stats' => $stats,
                'tasks' => $tasks,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch operations data', $e->getMessage(), 500);
        }
    }

    private function calculateTaskStats($tasks): array
    {
        $total = $tasks->count();
        $completed = $tasks->where('status', 'completed')->count();
        $inProgress = $tasks->where('status', 'in_progress')->count();
        $due = $tasks->filter(function ($task) {
            return $task->due_date && Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed';
        })->count();

        return [
            'total' => $total,
            'total_change' => 0, // Placeholder for historical comparison
            'completed' => $completed,
            'completed_change' => 0,
            'in_progress' => $inProgress,
            'in_progress_change' => 0,
            'due' => $due,
            'due_change' => 0,
        ];
    }

    private function transformTasks($tasks): array
    {
        return $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'task' => $task->title,
                'assigned_to' => $task->assignedTo->name ?? 'Unassigned',
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : null,
            ];
        })->toArray();
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
                return $this->notFoundResponse('No franchise found for this user');
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

            return $this->successResponse([
                'stats' => [
                    'total_milestones' => $totalMilestones,
                    'completed' => $completed,
                    'scheduled' => $scheduled,
                    'overdue' => $overdue,
                ],
                'timeline' => $timeline,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch timeline data', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
            }

            return $this->successResponse($franchise);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch franchise information', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
            }

            // Get franchisees who have units in this franchise OR are directly linked to this franchise
            $query = User::where('role', 'franchisee')
                ->where(function ($q) use ($franchise) {
                    $q->where('franchise_id', $franchise->id)
                        ->orWhereHas('units', function ($unitQuery) use ($franchise) {
                            $unitQuery->where('franchise_id', $franchise->id);
                        });
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
                    'nationality' => $franchisee->nationality,
                    'created_at' => $franchisee->created_at,
                ];
            });

            return $this->successResponse($franchisees);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch franchisees', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
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

            return $this->successResponse($units);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch units', $e->getMessage(), 500);
        }
    }

    /**
     * Get brokers for the franchisor
     */
    public function brokersIndex(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $query = User::where('role', 'broker')
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

            // Role filter removed - broker_role field no longer exists

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sort = $this->parseSortParams($request);
            $query->orderBy($sort['column'], $sort['order']);

            // Pagination
            $perPage = $request->get('perPage', 10);
            $brokers = $query->paginate($perPage);

            // Transform the data to include assignedLeads count
            $brokers->getCollection()->transform(function ($associate) {
                $name = $associate->name;

                return [
                    'id' => $associate->id,
                    'name' => $name,
                    'email' => $associate->email,
                    'phone' => $associate->phone,
                    'status' => $associate->status ?? 'active',
                    'nationality' => $associate->nationality,
                    'city' => $associate->city,
                    'assignedLeads' => $associate->leads_count,
                    'avatar' => $associate->avatar,
                    'avatarText' => $name ? strtoupper(substr($name, 0, 2)) : 'BR',
                ];
            });

            return $this->successResponse([
                'data' => $brokers->items(),
                'total' => $brokers->total(),
                'per_page' => $brokers->perPage(),
                'current_page' => $brokers->currentPage(),
                'last_page' => $brokers->lastPage(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch brokers', $e->getMessage(), 500);
        }
    }

    /**
     * Store a new broker
     */
    public function brokersStore(StoreBrokerRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $validatedData = $request->validated();

            $validatedData['role'] = 'broker';
            $validatedData['franchise_id'] = $franchise->id;
            $validatedData['password'] = bcrypt($validatedData['password']);

            $broker = User::create($validatedData);

            // Load the leads count
            $broker->loadCount('leads');

            return $this->successResponse([
                'id' => $broker->id,
                'name' => $broker->name,
                'email' => $broker->email,
                'phone' => $broker->phone,
                'status' => $broker->status,
                'nationality' => $broker->nationality,
                'state' => $broker->state,
                'city' => $broker->city,
                'assignedLeads' => $broker->leads_count,
                'avatar' => $broker->avatar,
                'avatarText' => strtoupper(substr($broker->name, 0, 2)),
            ], 'Broker created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create broker', $e->getMessage(), 500);
        }
    }

    /**
     * Show a specific broker
     */
    public function brokersShow($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $broker = User::where('role', 'broker')
                ->where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->withCount('leads')
                ->with(['leads' => function ($query) {
                    $query->select('id', 'assigned_to', 'first_name', 'last_name', 'email', 'status', 'priority');
                }])
                ->first();

            if (! $broker) {
                return $this->notFoundResponse('Broker not found');
            }

            return $this->successResponse([
                'id' => $broker->id,
                'name' => $broker->name,
                'email' => $broker->email,
                'phone' => $broker->phone,
                'status' => $broker->status,
                'nationality' => $broker->nationality,
                'state' => $broker->state,
                'city' => $broker->city,
                'assignedLeads' => $broker->leads_count,
                'avatar' => $broker->avatar,
                'avatarText' => strtoupper(substr($broker->name, 0, 2)),
                'leads' => $broker->leads,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch broker', $e->getMessage(), 500);
        }
    }

    /**
     * Update a broker
     */
    public function brokersUpdate(UpdateBrokerRequest $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $broker = User::where('role', 'broker')
                ->where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->first();

            if (! $broker) {
                return $this->notFoundResponse('Broker not found');
            }

            $validatedData = $request->validated();

            if (isset($validatedData['password'])) {
                $validatedData['password'] = bcrypt($validatedData['password']);
            }

            $broker->update($validatedData);

            // Load the leads count
            $broker->loadCount('leads');

            return $this->successResponse([
                'id' => $broker->id,
                'name' => $broker->name,
                'email' => $broker->email,
                'phone' => $broker->phone,
                'status' => $broker->status,
                'nationality' => $broker->nationality,
                'state' => $broker->state,
                'city' => $broker->city,
                'assignedLeads' => $broker->leads_count,
                'avatar' => $broker->avatar,
                'avatarText' => strtoupper(substr($broker->name, 0, 2)),
            ], 'Broker updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update broker', $e->getMessage(), 500);
        }
    }

    /**
     * Delete a broker
     */
    public function brokersDestroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $broker = User::where('role', 'broker')
                ->where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->first();

            if (! $broker) {
                return $this->notFoundResponse('Broker not found');
            }

            // Unassign leads before deleting the broker
            Lead::where('assigned_to', $id)->update(['assigned_to' => null]);

            $broker->delete();

            return $this->successResponse(null, 'Broker deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete broker', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
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

            return $this->successResponse([
                'is_complete' => $isComplete,
                'completion_percentage' => round($completionPercentage, 2),
                'completed_fields' => $completedFields,
                'total_fields' => $totalFields,
                'missing_fields' => $missingFields,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to check profile completion status', $e->getMessage(), 500);
        }
    }

    /**
     * Register a new franchise
     */
    public function registerFranchise(RegisterFranchiseRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user already has a franchise
            $existingFranchise = Franchise::where('franchisor_id', $user->id)->first();
            if ($existingFranchise) {
                return $this->errorResponse('User already has a franchise registered', null, 400);
            }

            // Get validated data from Form Request
            $validatedData = $request->validated();

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
                'nationality' => $validatedData['personalInfo']['nationality'],
            ]);

            return $this->successResponse([
                'franchise_id' => $franchise->id,
                'status' => $franchise->status,
            ], 'Franchise registered successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to register franchise', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
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
                'financialDetails' => [
                    'franchiseFee' => $franchise->franchise_fee ?? 0,
                    'royaltyPercentage' => $franchise->royalty_percentage ?? 0,
                    'marketingFeePercentage' => $franchise->marketing_fee_percentage ?? 0,
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

            return $this->successResponse([
                'franchise' => $franchiseData,
                'documents' => $documentsData,
                'products' => $productsData,
                'stats' => [
                    'status' => ucfirst($franchise->status),
                    'totalDocuments' => count($documentsData),
                    'totalProducts' => count($productsData),
                    'activeProducts' => count($productsData),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch franchise data', $e->getMessage(), 500);
        }
    }

    /**
     * Update franchise data
     */
    public function updateFranchise(UpdateFranchiseRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            // Get validated data from Form Request
            $validatedData = $request->validated();

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

                if (array_key_exists('financialDetails', $validatedData['franchiseDetails'])) {
                    if (array_key_exists('franchiseFee', $validatedData['franchiseDetails']['financialDetails'])) {
                        $updateData['franchise_fee'] = $validatedData['franchiseDetails']['financialDetails']['franchiseFee'];
                    }
                    if (array_key_exists('royaltyPercentage', $validatedData['franchiseDetails']['financialDetails'])) {
                        $updateData['royalty_percentage'] = $validatedData['franchiseDetails']['financialDetails']['royaltyPercentage'];
                    }
                    if (array_key_exists('marketingFeePercentage', $validatedData['franchiseDetails']['financialDetails'])) {
                        $updateData['marketing_fee_percentage'] = $validatedData['franchiseDetails']['financialDetails']['marketingFeePercentage'];
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
                if (array_key_exists('nationality', $validatedData['personalInfo'])) {
                    $userUpdateData['nationality'] = $validatedData['personalInfo']['nationality'];
                }
            }

            if (! empty($userUpdateData)) {
                $user->update($userUpdateData);
            }

            return $this->successResponse([
                'franchise_id' => $franchise->id,
            ], 'Franchise updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update franchise', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
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

            return $this->successResponse([
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
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch units statistics', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user');
            }

            $validatedData = $request->validate([
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Store the logo file
            $logoPath = $request->file('logo')->store('franchise-logos', 'public');
            $logoUrl = asset('storage/'.$logoPath);

            // Update franchise with new logo URL
            $franchise->update(['logo' => $logoUrl]);

            return $this->successResponse([
                'logo_url' => $logoUrl,
            ], 'Logo uploaded successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload logo', $e->getMessage(), 500);
        }
    }

    /**
     * Create a franchisee with associated unit for the authenticated franchisor
     */
    public function createFranchiseeWithUnit(CreateFranchiseeWithUnitRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $validatedData = $request->validated();

            DB::beginTransaction();

            // Generate random password for franchisee
            $temporaryPassword = \Illuminate\Support\Str::random(12);

            // Create franchisee user
            $franchisee = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($temporaryPassword),
                'role' => 'franchisee',
                'status' => 'active',
                'phone' => $validatedData['phone'],
                'city' => $validatedData['city'],
                'profile_completed' => false, // Require onboarding
            ]);

            // Generate unit code
            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $franchise->name), 0, 3));
            if (empty($prefix)) {
                $prefix = 'UNI';
            }

            $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $validatedData['unit_name']), 0, 6));
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
                'unit_name' => $validatedData['unit_name'],
                'unit_code' => $unitCode,
                'franchise_id' => $franchise->id, // Use authenticated franchisor's franchise
                'franchisee_id' => $franchisee->id, // Assign the new franchisee as unit manager
                'unit_type' => $validatedData['unit_type'],
                'address' => $validatedData['address'],
                'city' => $validatedData['city'],
                'state_province' => $validatedData['state_province'],
                'postal_code' => $validatedData['postal_code'],
                'nationality' => $validatedData['nationality'],
                'phone' => $validatedData['phone'],
                'email' => $validatedData['email'],
                'size_sqft' => $validatedData['size_sqft'],
                'monthly_rent' => $validatedData['monthly_rent'],
                'opening_date' => $validatedData['opening_date'],
                'status' => $validatedData['status'] ?? 'planning',
                'employee_count' => 0,
            ]);

            // Send email notification with login credentials
            $loginUrl = env('APP_URL').'/login';
            $franchisee->notify(new \App\Notifications\NewFranchiseeCredentials($temporaryPassword, $unitCode, $loginUrl));

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
            return $this->notFoundResponse('Unit not found or access denied');
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

        return $this->successResponse($staffMembers, 'Unit staff retrieved successfully');
    }

    /**
     * Assign a broker to a franchise
     */
    public function assignBroker(Request $request, Franchise $franchise): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify that the franchise belongs to the authenticated franchisor
            if ($franchise->franchisor_id !== $user->id) {
                return $this->forbiddenResponse('You do not have permission to modify this franchise');
            }

            $validated = $request->validate([
                'broker_id' => 'required|exists:users,id',
            ]);

            // Verify that the broker is indeed a broker role
            $broker = User::where('id', $validated['broker_id'])
                ->where('role', 'broker')
                ->first();

            if (!$broker) {
                return $this->validationErrorResponse(
                    ['broker_id' => ['Selected user is not a broker']],
                    'Invalid broker selected'
                );
            }

            $franchise->update(['broker_id' => $validated['broker_id']]);

            return $this->successResponse(
                $franchise->load(['broker:id,name,email,phone']),
                'Broker assigned successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to assign broker', $e->getMessage(), 500);
        }
    }

    /**
     * Toggle franchise marketplace listing status
     */
    public function toggleMarketplaceListing(Franchise $franchise): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify that the franchise belongs to the authenticated franchisor
            if ($franchise->franchisor_id !== $user->id) {
                return $this->forbiddenResponse('You do not have permission to modify this franchise');
            }

            $franchise->update([
                'is_marketplace_listed' => !$franchise->is_marketplace_listed
            ]);

            return $this->successResponse(
                [
                    'franchise_id' => $franchise->id,
                    'is_marketplace_listed' => $franchise->is_marketplace_listed,
                ],
                $franchise->is_marketplace_listed
                    ? 'Franchise is now listed in marketplace'
                    : 'Franchise removed from marketplace'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update marketplace listing', $e->getMessage(), 500);
        }
    }

    /**
     * Get franchise-level staff
     */
    public function getFranchiseStaff(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $query = Staff::byFranchise($franchise->id);

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('department')) {
                $query->where('department', $request->department);
            }

            if ($request->has('employment_type')) {
                $query->where('employment_type', $request->employment_type);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('job_title', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $staff = $query->paginate($perPage);

            // Transform staff data
            $transformedStaff = $staff->map(function ($staffMember) {
                return [
                    'id' => $staffMember->id,
                    'name' => $staffMember->name,
                    'email' => $staffMember->email,
                    'phone' => $staffMember->phone,
                    'jobTitle' => $staffMember->job_title,
                    'department' => $staffMember->department,
                    'salary' => $staffMember->salary,
                    'hireDate' => $staffMember->hire_date?->format('Y-m-d'),
                    'shiftStart' => $staffMember->shift_start?->format('H:i'),
                    'shiftEnd' => $staffMember->shift_end?->format('H:i'),
                    'status' => $staffMember->status,
                    'employmentType' => $staffMember->employment_type,
                    'notes' => $staffMember->notes,
                    'createdAt' => $staffMember->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return $this->successResponse([
                'data' => $transformedStaff,
                'current_page' => $staff->currentPage(),
                'last_page' => $staff->lastPage(),
                'per_page' => $staff->perPage(),
                'total' => $staff->total(),
            ], 'Staff retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch franchise staff', $e->getMessage(), 500);
        }
    }

    /**
     * Create franchise-level staff
     */
    public function createFranchiseStaff(\App\Http\Requests\StoreFranchiseStaffRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $validated = $request->validated();
            $validated['franchise_id'] = $franchise->id;

            $staff = Staff::create($validated);

            return $this->successResponse([
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
                'status' => $staff->status,
                'employmentType' => $staff->employment_type,
                'notes' => $staff->notes,
            ], 'Staff member created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create staff member', $e->getMessage(), 500);
        }
    }

    /**
     * Update franchise-level staff
     */
    public function updateFranchiseStaff(\App\Http\Requests\UpdateFranchiseStaffRequest $request, $staffId): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $staff = Staff::where('id', $staffId)
                ->where('franchise_id', $franchise->id)
                ->first();

            if (!$staff) {
                return $this->notFoundResponse('Staff member not found or does not belong to your franchise');
            }

            $validated = $request->validated();
            $staff->update($validated);

            return $this->successResponse([
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
                'status' => $staff->status,
                'employmentType' => $staff->employment_type,
                'notes' => $staff->notes,
            ], 'Staff member updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update staff member', $e->getMessage(), 500);
        }
    }

    /**
     * Delete franchise-level staff
     */
    public function deleteFranchiseStaff(Request $request, $staffId): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $staff = Staff::where('id', $staffId)
                ->where('franchise_id', $franchise->id)
                ->first();

            if (!$staff) {
                return $this->notFoundResponse('Staff member not found or does not belong to your franchise');
            }

            $staff->delete();

            return $this->successResponse(null, 'Staff member deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete staff member', $e->getMessage(), 500);
        }
    }

    /**
     * Get franchise staff statistics
     */
    public function getFranchiseStaffStatistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            $totalStaff = Staff::byFranchise($franchise->id)->count();
            $activeStaff = Staff::byFranchise($franchise->id)->where('status', 'active')->count();
            $onLeaveStaff = Staff::byFranchise($franchise->id)->where('status', 'on_leave')->count();

            // Group by department
            $byDepartment = Staff::byFranchise($franchise->id)
                ->whereNotNull('department')
                ->select('department', DB::raw('count(*) as count'))
                ->groupBy('department')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->department => $item->count];
                });

            return $this->successResponse([
                'totalStaff' => $totalStaff,
                'activeStaff' => $activeStaff,
                'onLeaveStaff' => $onLeaveStaff,
                'terminatedStaff' => Staff::byFranchise($franchise->id)->where('status', 'terminated')->count(),
                'fullTimeStaff' => Staff::byFranchise($franchise->id)->where('employment_type', 'full_time')->count(),
                'partTimeStaff' => Staff::byFranchise($franchise->id)->where('employment_type', 'part_time')->count(),
                'byDepartment' => $byDepartment,
            ], 'Staff statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch staff statistics', $e->getMessage(), 500);
        }
    }
}
