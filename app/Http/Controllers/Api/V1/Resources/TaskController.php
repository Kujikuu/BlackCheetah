<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Franchise;
use App\Models\Task;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends BaseResourceController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::with(['franchise', 'unit', 'assignedTo', 'createdBy']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('category')) {
            // Map frontend category to database type for filtering
            $categoryToTypeMap = [
                'Operations' => 'operations',
                'Training' => 'training',
                'Maintenance' => 'maintenance',
                'Marketing' => 'marketing',
                'Finance' => 'finance',
                'HR' => 'other', // Map HR to 'other' since 'hr' is not in database enum
                'Quality Control' => 'compliance',
                'Customer Service' => 'support',
            ];

            $category = $request->category;
            $type = $categoryToTypeMap[$category] ?? null;
            if ($type) {
                $query->where('type', $type);
            }
        }

        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('task_number', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sort = $this->parseSortParams($request, 'created_at');
        $query->orderBy($sort['column'], $sort['order']);

        // Pagination
        $perPage = $this->getPaginationParams($request);
        $tasks = $query->paginate($perPage);

        return $this->successResponse($tasks, 'Tasks retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Set the created_by field to the authenticated user
        $validated['created_by'] = $user->id;

        // If franchise_id is not provided, try to set it based on the authenticated user
        if (! isset($validated['franchise_id'])) {
            // If user is a franchisor, set franchise_id to their franchise
            if ($user->role === 'franchisor') {
                $franchise = Franchise::where('franchisor_id', $user->id)->first();
                if ($franchise) {
                    $validated['franchise_id'] = $franchise->id;
                }
            }
            // If user is a franchisee, set franchise_id to their unit's franchise
            elseif ($user->role === 'franchisee') {
                $unit = Unit::where('franchisee_id', $user->id)->first();
                if ($unit) {
                    $validated['franchise_id'] = $unit->franchise_id;
                    $validated['unit_id'] = $unit->id;
                    
                    // Auto-assign to franchisor if not explicitly assigned
                    if (!isset($validated['assigned_to'])) {
                        $franchise = Franchise::find($unit->franchise_id);
                        if ($franchise) {
                            $validated['assigned_to'] = $franchise->franchisor_id;
                        }
                    }
                }
            }
        }

        // Map frontend category to database type
        $categoryToTypeMap = [
            'Operations' => 'operations',
            'Training' => 'training',
            'Maintenance' => 'maintenance',
            'Marketing' => 'marketing',
            'Finance' => 'finance',
            'HR' => 'other', // Map HR to 'other' since 'hr' is not in database enum
            'Quality Control' => 'compliance',
            'Customer Service' => 'support',
        ];

        $validated['type'] = $categoryToTypeMap[$validated['category']] ?? 'other';
        unset($validated['category']); // Remove category as it doesn't exist in database

        $task = Task::create($validated);

        return $this->successResponse(
            $task->load(['franchise', 'unit', 'assignedTo', 'createdBy']),
            'Task created successfully',
            201
        );

    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {
        $task->load(['franchise', 'unit', 'assignedTo', 'createdBy']);

        return $this->successResponse($task, 'Task retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();

        // Map frontend category to database type if provided
        if (isset($validated['category'])) {
            $categoryToTypeMap = [
                'Operations' => 'operations',
                'Training' => 'training',
                'Maintenance' => 'maintenance',
                'Marketing' => 'marketing',
                'Finance' => 'finance',
                'HR' => 'other', // Map HR to 'other' since 'hr' is not in database enum
                'Quality Control' => 'compliance',
                'Customer Service' => 'support',
            ];

            $validated['type'] = $categoryToTypeMap[$validated['category']] ?? 'other';
            unset($validated['category']); // Remove category as it doesn't exist in database
        }

        $task->update($validated);

        return $this->successResponse(
            $task->load(['franchise', 'unit', 'assignedTo', 'createdBy']),
            'Task updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return $this->successResponse(null, 'Task deleted successfully');
    }

    /**
     * Complete task
     */
    public function complete(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'actual_hours' => 'nullable|numeric|min:0',
            'completion_notes' => 'nullable|string',
        ]);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_hours' => $validated['actual_hours'] ?? $task->actual_hours,
            'notes' => $validated['completion_notes'] ?
                ($task->notes ? $task->notes."\n\nCompletion Notes: ".$validated['completion_notes'] :
                'Completion Notes: '.$validated['completion_notes']) : $task->notes,
        ]);

        return $this->successResponse($task, 'Task completed successfully');
    }

    /**
     * Start task
     */
    public function start(Task $task): JsonResponse
    {
        $task->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return $this->successResponse($task, 'Task started successfully');
    }

    /**
     * Assign task to user
     */
    public function assign(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task->update($validated);

        return $this->successResponse($task->load(['assignedTo']), 'Task assigned successfully');
    }

    /**
     * Update task progress
     */
    public function updateProgress(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'completion_percentage' => 'required|integer|min:0|max:100',
            'progress_notes' => 'nullable|string',
        ]);

        $updateData = [
            'completion_percentage' => $validated['completion_percentage'],
        ];

        if ($validated['progress_notes']) {
            $currentNotes = $task->notes ?? '';
            $newNote = '['.now()->format('Y-m-d H:i:s').'] Progress Update: '.$validated['progress_notes'];
            $updateData['notes'] = $currentNotes ? $currentNotes."\n\n".$newNote : $newNote;
        }

        $task->update($updateData);

        return $this->successResponse($task, 'Task progress updated successfully');
    }

    /**
     * Get task statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Task::query();

        // Apply filters
        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $stats = [
            'total_tasks' => $query->count(),
            'pending_tasks' => $query->where('status', 'pending')->count(),
            'in_progress_tasks' => $query->where('status', 'in_progress')->count(),
            'completed_tasks' => $query->where('status', 'completed')->count(),
            'overdue_tasks' => $query->where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completion_rate' => $query->count() > 0 ?
                round(($query->where('status', 'completed')->count() / $query->count()) * 100, 2) : 0,
            'tasks_by_category' => $query->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->pluck('count', 'category'),
            'tasks_by_priority' => $query->groupBy('priority')
                ->selectRaw('priority, count(*) as count')
                ->pluck('count', 'priority'),
            'average_completion_time' => $query->whereNotNull('completed_at')
                ->whereNotNull('started_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours')
                ->value('avg_hours'),
        ];

        return $this->successResponse($stats, 'Task statistics retrieved successfully');
    }

    /**
     * Get current user's tasks (for franchise owners - bidirectional)
     */
    public function myTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return $this->notFoundResponse('No franchise found for current user');
        }

        // Get tasks where user is creator OR assignee (bidirectional)
        $query = Task::where('franchise_id', $franchise->id)
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
            })
            ->with(['franchise', 'unit', 'assignedTo', 'createdBy']);

        // Apply filters
        if ($request->has('filter')) {
            $filter = $request->filter;
            if ($filter === 'created') {
                $query->where('created_by', $user->id);
            } elseif ($filter === 'assigned') {
                $query->where('assigned_to', $user->id);
            }
            // 'all' or no filter shows both
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($tasks, 'Tasks retrieved successfully');
    }

    /**
     * Get current user's unit tasks (for unit managers)
     */
    public function myUnitTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        $tasks = Task::where('unit_id', $unit->id)
            ->with(['franchise', 'unit', 'assignedTo', 'createdBy'])
            ->paginate(15);

        return $this->successResponse($tasks, 'Unit tasks retrieved successfully');
    }

    /**
     * Get current user's assigned tasks (for employees)
     */
    public function myAssignedTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $tasks = Task::where('assigned_to', $user->id)
            ->with(['franchise', 'unit', 'assignedTo', 'createdBy'])
            ->paginate(15);

        return $this->successResponse($tasks, 'Assigned tasks retrieved successfully');
    }

    /**
     * Get broker user's tasks with statistics
     */
    public function myBrokerTasks(Request $request): JsonResponse
    {
        $user = $request->user();

        // Build query for tasks assigned to this broker user
        $query = Task::where('assigned_to', $user->id);

        // If broker belongs to a franchise, filter by franchise
        if ($user->franchise_id) {
            $query->where('franchise_id', $user->franchise_id);
        }

        $query->with(['franchise', 'unit', 'assignedTo', 'createdBy', 'lead']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('category')) {
            $categoryToTypeMap = [
                'Lead Management' => 'lead_management',
                'Sales' => 'sales',
                'Market Research' => 'market_research',
                'Onboarding' => 'onboarding',
                'Operations' => 'operations',
                'Training' => 'training',
                'Marketing' => 'marketing',
            ];

            $type = $categoryToTypeMap[$request->category] ?? null;
            if ($type) {
                $query->where('type', $type);
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Get paginated tasks
        $perPage = $request->get('per_page', 15);
        $tasks = $query->paginate($perPage);

        // Transform tasks for frontend
        $transformedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'category' => $task->getCategoryForBroker(),
                'assignedTo' => $task->assignedTo ? $task->assignedTo->name : 'Unassigned',
                'unitName' => $task->unit ? $task->unit->name : 'N/A',
                'startDate' => $task->created_at->format('Y-m-d'),
                'dueDate' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'priority' => $task->priority,
                'status' => $task->status,
                'leadId' => $task->lead_id,
                'leadName' => $task->lead ? "{$task->lead->first_name} {$task->lead->last_name}" : null,
            ];
        });

        return $this->successResponse([
            'data' => $transformedTasks,
            'pagination' => [
                'total' => $tasks->total(),
                'perPage' => $tasks->perPage(),
                'currentPage' => $tasks->currentPage(),
                'lastPage' => $tasks->lastPage(),
            ],
        ], 'Tasks retrieved successfully');
    }

    /**
     * Get broker tasks statistics
     */
    public function brokerTasksStatistics(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get tasks for this broker user
        $query = Task::where('assigned_to', $user->id);

        // If broker belongs to a franchise, filter by franchise
        if ($user->franchise_id) {
            $query->where('franchise_id', $user->franchise_id);
        }

        $allTasks = $query->get();

        $totalTasks = $allTasks->count();
        $completedTasks = $allTasks->where('status', 'completed')->count();
        $inProgressTasks = $allTasks->where('status', 'in_progress')->count();

        // Calculate due/overdue tasks
        $today = now();
        $dueTasks = $allTasks->filter(function ($task) use ($today) {
            return $task->due_date &&
                   $task->due_date->lte($today) &&
                   $task->status !== 'completed';
        })->count();

        return $this->successResponse([
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'inProgressTasks' => $inProgressTasks,
            'dueTasks' => $dueTasks,
        ], 'Statistics retrieved successfully');
    }

    /**
     * Update broker user's task status
     */
    public function updateBrokerTaskStatus(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        // Verify the task belongs to this user
        if ($task->assigned_to !== $user->id) {
            return $this->forbiddenResponse('Unauthorized to update this task');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->status = $validated['status'];

        // Set timestamps based on status
        if ($validated['status'] === 'in_progress' && ! $task->started_at) {
            $task->started_at = now();
        }

        if ($validated['status'] === 'completed' && ! $task->completed_at) {
            $task->completed_at = now();
        }

        $task->save();

        return $this->successResponse($task, 'Task status updated successfully');
    }
}
