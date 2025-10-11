<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Task;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
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
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tasks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tasks,
            'message' => 'Tasks retrieved successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|in:Operations,Training,Maintenance,Marketing,Finance,HR,Quality Control,Customer Service',
                'priority' => 'required|in:low,medium,high',
                'status' => 'required|in:pending,in_progress,completed',
                'franchise_id' => 'nullable|exists:franchises,id',
                'unit_id' => 'nullable|exists:units,id',
                'assigned_to' => 'nullable|exists:users,id',
                'due_date' => 'nullable|date|after_or_equal:today',
                'estimated_hours' => 'nullable|numeric|min:0',
                'actual_hours' => 'nullable|numeric|min:0',
                'completion_percentage' => 'nullable|integer|min:0|max:100',
                'attachments' => 'nullable|array',
                'checklist' => 'nullable|array',
                'dependencies' => 'nullable|array',
                'tags' => 'nullable|array',
                'notes' => 'nullable|string',
            ]);

            // Set the created_by field to the authenticated user
            $validated['created_by'] = $request->user()->id;

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

            return response()->json([
                'success' => true,
                'data' => $task->load(['franchise', 'unit', 'assignedTo', 'createdBy']),
                'message' => 'Task created successfully',
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'data_received' => $request->all(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {
        $task->load(['franchise', 'unit', 'assignedTo', 'createdBy']);

        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Task retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category' => 'sometimes|in:Operations,Training,Maintenance,Marketing,Finance,HR,Quality Control,Customer Service',
            'priority' => 'sometimes|in:low,medium,high',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'attachments' => 'nullable|array',
            'checklist' => 'nullable|array',
            'dependencies' => 'nullable|array',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

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

        return response()->json([
            'success' => true,
            'data' => $task->load(['franchise', 'unit', 'assignedTo', 'createdBy']),
            'message' => 'Task updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
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
            'completion_percentage' => 100,
            'completed_at' => now(),
            'actual_hours' => $validated['actual_hours'] ?? $task->actual_hours,
            'notes' => $validated['completion_notes'] ?
                ($task->notes ? $task->notes."\n\nCompletion Notes: ".$validated['completion_notes'] :
                'Completion Notes: '.$validated['completion_notes']) : $task->notes,
        ]);

        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Task completed successfully',
        ]);
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

        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Task started successfully',
        ]);
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

        return response()->json([
            'success' => true,
            'data' => $task->load(['assignedTo']),
            'message' => 'Task assigned successfully',
        ]);
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

        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Task progress updated successfully',
        ]);
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

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Task statistics retrieved successfully',
        ]);
    }

    /**
     * Get current user's tasks (for franchise owners)
     */
    public function myTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user',
            ], 404);
        }

        $tasks = Task::where('franchise_id', $franchise->id)
            ->with(['franchise', 'unit', 'assignedTo', 'createdBy'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tasks,
            'message' => 'Tasks retrieved successfully',
        ]);
    }

    /**
     * Get current user's unit tasks (for unit managers)
     */
    public function myUnitTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
        }

        $tasks = Task::where('unit_id', $unit->id)
            ->with(['franchise', 'unit', 'assignedTo', 'createdBy'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tasks,
            'message' => 'Unit tasks retrieved successfully',
        ]);
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

        return response()->json([
            'success' => true,
            'data' => $tasks,
            'message' => 'Assigned tasks retrieved successfully',
        ]);
    }
}
