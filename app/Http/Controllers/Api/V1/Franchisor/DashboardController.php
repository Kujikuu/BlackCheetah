<?php

namespace App\Http\Controllers\Api\V1\Franchisor;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Models\Franchise;
use App\Models\Lead;
use App\Models\Revenue;
use App\Models\Royalty;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Franchisor Dashboard Controller
 * 
 * Handles dashboard statistics and analytics for franchisors
 */
class DashboardController extends BaseResourceController
{
    /**
     * Get franchisor dashboard statistics
     */
    public function stats(): JsonResponse
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
                ->where('franchise_id', $franchise->id)
                ->count();

            // Total units under this franchisor
            $totalUnits = Unit::where('franchise_id', $franchise->id)->count();

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

            // Get the most recent month with data, fallback to current month
            $latestRevenue = Revenue::where('franchise_id', $franchise->id)
                ->where('status', 'verified')
                ->where('payment_status', 'completed')
                ->orderBy('period_year', 'desc')
                ->orderBy('period_month', 'desc')
                ->first();

            // Use latest month with data, or current month if no data exists
            $analysisMonth = $latestRevenue ? $latestRevenue->period_month : $currentMonth->month;
            $analysisYear = $latestRevenue ? $latestRevenue->period_year : $currentMonth->year;

            // Sales: Sum of verified/completed revenue for analysis month
            $currentMonthSales = Revenue::where('franchise_id', $franchise->id)
                ->where('period_year', $analysisYear)
                ->where('period_month', $analysisMonth)
                ->where('status', 'verified')
                ->where('payment_status', 'completed')
                ->sum('net_amount');

            // Expenses: Sum of expense transactions + monthly unit expenses for analysis month
            $currentMonthExpenses = Transaction::where('franchise_id', $franchise->id)
                ->whereYear('transaction_date', $analysisYear)
                ->whereMonth('transaction_date', $analysisMonth)
                ->whereIn('type', ['expense', 'royalty', 'marketing_fee'])
                ->where('status', 'completed')
                ->sum('amount');

            // Add monthly unit expenses
            $monthlyUnitExpenses = Unit::where('franchise_id', $franchise->id)->sum('monthly_expenses');

            $totalExpenses = $currentMonthExpenses + $monthlyUnitExpenses;

            // Profit: Sales - Expenses
            $currentMonthProfit = $currentMonthSales - $totalExpenses;

            // Pending royalties
            $pendingRoyalties = Royalty::whereHas('unit.franchise', function ($query) use ($franchise) {
                $query->where('franchisor_id', $franchise->id);
            })
                ->where('status', 'pending')
                ->sum('total_amount');

            // Unit statistics by status
            $activeUnits = Unit::where('franchise_id', $franchise->id)
                ->whereIn('status', ['active', 'operational'])
                ->count();

            $inactiveUnits = $totalUnits - $activeUnits;

            // Task statistics by status
            $completedTasks = Task::where('franchise_id', $franchise->id)
                ->where('status', 'completed')
                ->count();

            $pendingTasks = Task::where('franchise_id', $franchise->id)
                ->where('status', 'pending')
                ->count();

            $inProgressTasks = Task::where('franchise_id', $franchise->id)
                ->where('status', 'in_progress')
                ->count();

            $totalTasks = Task::where('franchise_id', $franchise->id)
                ->count();

            return $this->successResponse([
                'totalFranchisees' => (int) $totalFranchisees,
                'totalUnits' => (int) $totalUnits,
                'activeUnits' => (int) $activeUnits,
                'inactiveUnits' => (int) $inactiveUnits,
                'totalLeads' => (int) $totalLeads,
                'activeTasks' => (int) $activeTasks,
                'completedTasks' => (int) $completedTasks,
                'pendingTasks' => (int) $pendingTasks,
                'totalTasks' => (int) $totalTasks,
                'currentMonthRevenue' => (float) $currentMonthRevenue,
                'currentMonthSales' => (float) $currentMonthSales,
                'currentMonthExpenses' => (float) $totalExpenses,
                'currentMonthProfit' => (float) $currentMonthProfit,
                'revenueChange' => (float) round($revenueChange, 2),
                'pendingRoyalties' => (float) $pendingRoyalties,
            ], 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch dashboard statistics', 500, $e->getMessage());
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
            ], 'Operations data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch operations data', 500, $e->getMessage());
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
                return $this->notFoundResponse('No franchise found for this user');
            }

            // Recent activities (last 30 days)
            $activities = collect();

            // 1. Units - Opening and milestones
            $recentUnits = \App\Models\Unit::where('franchise_id', $franchise->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($unit) {
                    $status = 'completed';
                    if ($unit->status === 'active' && $unit->opening_date && Carbon::parse($unit->opening_date)->isFuture()) {
                        $status = 'scheduled';
                    }
                    
                    return [
                        'id' => 'unit-'.$unit->id,
                        'title' => "New Unit: {$unit->unit_name}",
                        'description' => "Unit opened in {$unit->city}, {$unit->nationality}",
                        'week' => 'Week '.Carbon::parse($unit->created_at)->weekOfYear,
                        'date' => Carbon::parse($unit->created_at)->format('M d, Y'),
                        'status' => $status,
                        'icon' => 'tabler-building-store',
                        'created_at' => $unit->created_at->toISOString(),
                    ];
                });

            // 2. Royalty Payments
            $recentRoyalties = \App\Models\Royalty::where('franchise_id', $franchise->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($royalty) {
                    $status = match($royalty->status) {
                        'paid' => 'completed',
                        'pending' => Carbon::parse($royalty->due_date)->isPast() ? 'overdue' : 'scheduled',
                        default => 'scheduled',
                    };
                    
                    return [
                        'id' => 'royalty-'.$royalty->id,
                        'title' => "Royalty Payment - ".number_format($royalty->total_amount, 2)." SAR",
                        'description' => "Payment for period: ".Carbon::parse($royalty->period_start_date)->format('M Y'),
                        'week' => 'Week '.Carbon::parse($royalty->created_at)->weekOfYear,
                        'date' => Carbon::parse($royalty->created_at)->format('M d, Y'),
                        'status' => $status,
                        'icon' => 'tabler-coins',
                        'created_at' => $royalty->created_at->toISOString(),
                    ];
                });

            // 3. Technical Requests
            $recentTechnicalRequests = \App\Models\TechnicalRequest::whereHas('requester', function ($query) use ($franchise) {
                    $query->where('franchise_id', $franchise->id);
                })
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($request) {
                    $status = match($request->status) {
                        'resolved', 'closed' => 'completed',
                        'open', 'in_progress' => 'scheduled',
                        default => 'scheduled',
                    };
                    
                    return [
                        'id' => 'tech-'.$request->id,
                        'title' => "Technical Request: {$request->title}",
                        'description' => "Category: {$request->category} - Priority: {$request->priority}",
                        'week' => 'Week '.Carbon::parse($request->created_at)->weekOfYear,
                        'date' => Carbon::parse($request->created_at)->format('M d, Y'),
                        'status' => $status,
                        'icon' => 'tabler-tool',
                        'created_at' => $request->created_at->toISOString(),
                    ];
                });

            // 4. Recent leads
            $recentLeads = Lead::where('franchise_id', $franchise->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($lead) {
                    $status = match($lead->status) {
                        'converted' => 'completed',
                        'qualified' => 'scheduled',
                        'lost' => 'overdue',
                        default => 'scheduled',
                    };
                    
                    return [
                        'id' => 'lead-'.$lead->id,
                        'title' => "New Lead: {$lead->first_name} {$lead->last_name}",
                        'description' => "Lead from {$lead->lead_source} - Status: {$lead->status}",
                        'week' => 'Week '.Carbon::parse($lead->created_at)->weekOfYear,
                        'date' => Carbon::parse($lead->created_at)->format('M d, Y'),
                        'status' => $status,
                        'icon' => 'tabler-user-plus',
                        'created_at' => $lead->created_at->toISOString(),
                    ];
                });

            // 5. Recent tasks
            $recentTasks = Task::where('franchise_id', $franchise->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($task) {
                    $status = match($task->status) {
                        'completed' => 'completed',
                        'cancelled' => 'overdue',
                        default => Carbon::parse($task->due_date ?? now())->isPast() ? 'overdue' : 'scheduled',
                    };
                    
                    return [
                        'id' => 'task-'.$task->id,
                        'title' => "Task: {$task->title}",
                        'description' => $task->description ?? "Priority: {$task->priority}",
                        'week' => 'Week '.Carbon::parse($task->created_at)->weekOfYear,
                        'date' => Carbon::parse($task->created_at)->format('M d, Y'),
                        'status' => $status,
                        'icon' => 'tabler-checklist',
                        'created_at' => $task->created_at->toISOString(),
                    ];
                });

            // 6. Documents uploaded
            $recentDocuments = \App\Models\Document::where('franchise_id', $franchise->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($document) {
                    return [
                        'id' => 'doc-'.$document->id,
                        'title' => "Document Uploaded: {$document->name}",
                        'description' => "Type: {$document->type}",
                        'week' => 'Week '.Carbon::parse($document->created_at)->weekOfYear,
                        'date' => Carbon::parse($document->created_at)->format('M d, Y'),
                        'status' => 'completed',
                        'icon' => 'tabler-file-text',
                        'created_at' => $document->created_at->toISOString(),
                    ];
                });

            // 7. Reviews received
            $recentReviews = \App\Models\Review::whereHas('unit', function ($query) use ($franchise) {
                    $query->where('franchise_id', $franchise->id);
                })
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($review) {
                    $status = $review->rating >= 4 ? 'completed' : 'scheduled';
                    
                    return [
                        'id' => 'review-'.$review->id,
                        'title' => "Customer Review: {$review->rating}/5 stars",
                        'description' => "By {$review->customer_name} - {$review->sentiment}",
                        'week' => 'Week '.Carbon::parse($review->created_at)->weekOfYear,
                        'date' => Carbon::parse($review->created_at)->format('M d, Y'),
                        'status' => $status,
                        'icon' => 'tabler-star',
                        'created_at' => $review->created_at->toISOString(),
                    ];
                });

            // Combine and sort all activities
            $allActivities = $recentUnits
                ->merge($recentRoyalties)
                ->merge($recentTechnicalRequests)
                ->merge($recentLeads)
                ->merge($recentTasks)
                ->merge($recentDocuments)
                ->merge($recentReviews)
                ->sortByDesc('created_at')
                ->take(50) // Limit to 50 most recent activities
                ->values();

            // Calculate statistics
            $totalMilestones = $allActivities->count();
            $completed = $allActivities->where('status', 'completed')->count();
            $scheduled = $allActivities->where('status', 'scheduled')->count();
            $overdue = $allActivities->where('status', 'overdue')->count();

            return $this->successResponse([
                'timeline' => $allActivities,
                'stats' => [
                    'total_milestones' => $totalMilestones,
                    'completed' => $completed,
                    'scheduled' => $scheduled,
                    'overdue' => $overdue,
                ],
            ], 'Timeline data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch timeline data', 500, $e->getMessage());
        }
    }

    /**
     * Calculate task statistics for a collection of tasks
     */
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

    /**
     * Transform tasks for frontend display
     */
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
}
