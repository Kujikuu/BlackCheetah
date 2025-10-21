<?php

namespace App\Http\Controllers\Api\V1\Franchisor;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Models\Franchise;
use App\Models\Lead;
use App\Models\Revenue;
use App\Models\Royalty;
use App\Models\Task;
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

            $salesTasks = Task::where('franchise_id', $franchise->id)
                ->whereHas('assignedTo', function ($q) {
                    $q->where('role', 'sales');
                })
                ->with(['assignedTo', 'createdBy'])
                ->get();

            // Staff tasks (placeholder for now)
            $staffTasks = collect([]);

            // Calculate statistics for each role
            $stats = [
                'franchisee' => $this->calculateTaskStats($franchiseeTasks),
                'sales' => $this->calculateTaskStats($salesTasks),
                'staff' => $this->calculateTaskStats($staffTasks),
            ];

            // Transform tasks for frontend
            $tasks = [
                'franchisee' => $this->transformTasks($franchiseeTasks),
                'sales' => $this->transformTasks($salesTasks),
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

            // Combine and sort all activities
            $allActivities = $recentLeads->merge($recentTasks)
                ->sortByDesc('created_at')
                ->values();

            return $this->successResponse([
                'activities' => $allActivities,
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
