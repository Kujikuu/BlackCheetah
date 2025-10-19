<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\TechnicalRequestResource;
use App\Models\Franchise;
use App\Models\TechnicalRequest;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechnicalRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = TechnicalRequest::with(['requester', 'assignedUser', 'franchise', 'unit']);

        // Apply role-based filtering
        switch ($user->role) {
            case 'admin':
                // Admin can see all requests - no additional filtering needed
                break;

            case 'franchisor':
                // Franchisor can see requests from their franchises
                $franchiseIds = Franchise::where('franchisor_id', $user->id)->pluck('id');
                if ($franchiseIds->isEmpty()) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'data' => [],
                            'current_page' => 1,
                            'last_page' => 1,
                            'per_page' => 15,
                            'total' => 0,
                        ],
                        'message' => 'No franchises found for current user',
                    ]);
                }
                $query->whereIn('franchise_id', $franchiseIds);
                break;

            case 'franchisee':
                // Franchisee can see requests from their unit
                $unit = Unit::where('franchisee_id', $user->id)->first();
                if (!$unit) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'data' => [],
                            'current_page' => 1,
                            'last_page' => 1,
                            'per_page' => 15,
                            'total' => 0,
                        ],
                        'message' => 'No unit found for current user',
                    ]);
                }
                $query->where('unit_id', $unit->id);
                break;

            case 'sales':
                // Sales can see requests assigned to them or created by them
                $query->where(function ($q) use ($user) {
                    $q->where('requester_id', $user->id)
                        ->orWhere('assigned_to', $user->id);
                });
                break;

            default:
                // For any other roles, return empty result
                return response()->json([
                    'success' => true,
                    'data' => [
                        'data' => [],
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 15,
                        'total' => 0,
                    ],
                    'message' => 'Access denied for this role',
                ]);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
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
                    ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $requests = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $requests,
            'message' => 'Technical requests retrieved successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:hardware,software,network,pos_system,website,mobile_app,training,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,in_progress,resolved,closed,cancelled',
            'requester_id' => 'required|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'affected_system' => 'nullable|string|max:255',
            'steps_to_reproduce' => 'nullable|string',
            'expected_behavior' => 'nullable|string',
            'actual_behavior' => 'nullable|string',
            'browser_version' => 'nullable|string|max:100',
            'operating_system' => 'nullable|string|max:100',
            'device_type' => 'nullable|string|max:100',
            'attachments' => 'nullable|array',
            'internal_notes' => 'nullable|string',
        ]);

        $technicalRequest = TechnicalRequest::create($validated);

        return response()->json([
            'success' => true,
            'data' => new TechnicalRequestResource($technicalRequest->load(['requester', 'assignedUser', 'franchise', 'unit'])),
            'message' => 'Technical request created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TechnicalRequest $technicalRequest): JsonResponse
    {
        $technicalRequest->load(['requester', 'assignedUser', 'franchise', 'unit']);

        return response()->json([
            'success' => true,
            'data' => new TechnicalRequestResource($technicalRequest),
            'message' => 'Technical request retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category' => 'sometimes|in:hardware,software,network,pos_system,website,mobile_app,training,other',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:open,in_progress,resolved,closed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'affected_system' => 'nullable|string|max:255',
            'steps_to_reproduce' => 'nullable|string',
            'expected_behavior' => 'nullable|string',
            'actual_behavior' => 'nullable|string',
            'browser_version' => 'nullable|string|max:100',
            'operating_system' => 'nullable|string|max:100',
            'device_type' => 'nullable|string|max:100',
            'attachments' => 'nullable|array',
            'internal_notes' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
        ]);

        $technicalRequest->update($validated);

        return response()->json([
            'success' => true,
            'data' => new TechnicalRequestResource($technicalRequest->load(['requester', 'assignedUser', 'franchise', 'unit'])),
            'message' => 'Technical request updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TechnicalRequest $technicalRequest): JsonResponse
    {
        $technicalRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Technical request deleted successfully',
        ]);
    }

    /**
     * Bulk delete technical requests
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:technical_requests,id',
        ]);

        $count = TechnicalRequest::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} technical request(s) deleted successfully",
            'count' => $count,
        ]);
    }

    /**
     * Assign technical request to user
     */
    public function assign(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $technicalRequest->assignTo($validated['assigned_to']);

        return response()->json([
            'success' => true,
            'data' => $technicalRequest->load(['assignedUser']),
            'message' => 'Technical request assigned successfully',
        ]);
    }

    /**
     * Respond to technical request
     */
    public function respond(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $validated = $request->validate([
            'response_notes' => 'required|string',
        ]);

        $technicalRequest->respond($validated['response_notes']);

        return response()->json([
            'success' => true,
            'data' => $technicalRequest,
            'message' => 'Response added successfully',
        ]);
    }

    /**
     * Resolve technical request
     */
    public function resolve(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $validated = $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $technicalRequest->resolve($validated['resolution_notes']);

        return response()->json([
            'success' => true,
            'data' => $technicalRequest,
            'message' => 'Technical request resolved successfully',
        ]);
    }

    /**
     * Close technical request
     */
    public function close(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $validated = $request->validate([
            'satisfaction_rating' => 'nullable|integer|min:1|max:5',
            'satisfaction_feedback' => 'nullable|string',
        ]);

        $technicalRequest->close(
            $validated['satisfaction_rating'] ?? null,
            $validated['satisfaction_feedback'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => $technicalRequest,
            'message' => 'Technical request closed successfully',
        ]);
    }

    /**
     * Escalate technical request
     */
    public function escalate(TechnicalRequest $technicalRequest): JsonResponse
    {
        $technicalRequest->escalate();

        return response()->json([
            'success' => true,
            'data' => $technicalRequest,
            'message' => 'Technical request escalated successfully',
        ]);
    }

    /**
     * Add attachment to technical request
     */
    public function addAttachment(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $validated = $request->validate([
            'attachment_url' => 'required|string|max:500',
            'attachment_name' => 'required|string|max:255',
        ]);

        $technicalRequest->addAttachment($validated['attachment_url'], $validated['attachment_name']);

        return response()->json([
            'success' => true,
            'data' => $technicalRequest,
            'message' => 'Attachment added successfully',
        ]);
    }

    /**
     * Get technical request statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = TechnicalRequest::query();

        // Apply role-based filtering for statistics
        switch ($user->role) {
            case 'admin':
                // Admin can see all statistics - no additional filtering needed
                break;

            case 'franchisor':
                // Franchisor can see statistics for their franchises
                $franchiseIds = Franchise::where('franchisor_id', $user->id)->pluck('id');
                if ($franchiseIds->isEmpty()) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'total_requests' => 0,
                            'open_requests' => 0,
                            'resolved_requests' => 0,
                            'overdue_requests' => 0,
                            'escalated_requests' => 0,
                            'high_priority_requests' => 0,
                            'average_response_time' => 0,
                            'average_resolution_time' => 0,
                            'requests_by_category' => [],
                            'requests_by_priority' => [],
                            'satisfaction_ratings' => [],
                        ],
                        'message' => 'No franchises found for current user',
                    ]);
                }
                $query->whereIn('franchise_id', $franchiseIds);
                break;

            case 'franchisee':
                // Franchisee can see statistics for their unit
                $unit = Unit::where('franchisee_id', $user->id)->first();
                if (!$unit) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'total_requests' => 0,
                            'open_requests' => 0,
                            'resolved_requests' => 0,
                            'overdue_requests' => 0,
                            'escalated_requests' => 0,
                            'high_priority_requests' => 0,
                            'average_response_time' => 0,
                            'average_resolution_time' => 0,
                            'requests_by_category' => [],
                            'requests_by_priority' => [],
                            'satisfaction_ratings' => [],
                        ],
                        'message' => 'No unit found for current user',
                    ]);
                }
                $query->where('unit_id', $unit->id);
                break;

            case 'sales':
                // Sales can see statistics for requests assigned to them or created by them
                $query->where(function ($q) use ($user) {
                    $q->where('requester_id', $user->id)
                        ->orWhere('assigned_to', $user->id);
                });
                break;

            default:
                return response()->json([
                    'success' => true,
                    'data' => [
                        'total_requests' => 0,
                        'open_requests' => 0,
                        'resolved_requests' => 0,
                        'overdue_requests' => 0,
                        'escalated_requests' => 0,
                        'high_priority_requests' => 0,
                        'average_response_time' => 0,
                        'average_resolution_time' => 0,
                        'requests_by_category' => [],
                        'requests_by_priority' => [],
                        'satisfaction_ratings' => [],
                    ],
                    'message' => 'Access denied for this role',
                ]);
        }

        // Apply additional filters if provided
        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $stats = [
            'total_requests' => $query->count(),
            'open_requests' => $query->open()->count(),
            'resolved_requests' => $query->resolved()->count(),
            'overdue_requests' => $query->overdue()->count(),
            'escalated_requests' => $query->escalated()->count(),
            'high_priority_requests' => $query->highPriority()->count(),
            'average_response_time' => $query->whereNotNull('first_response_at')
                ->avg('response_time_hours'),
            'average_resolution_time' => $query->whereNotNull('resolved_at')
                ->avg('resolution_time_hours'),
            'requests_by_category' => $query->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->pluck('count', 'category'),
            'requests_by_priority' => $query->groupBy('priority')
                ->selectRaw('priority, count(*) as count')
                ->pluck('count', 'priority'),
            'satisfaction_ratings' => $query->whereNotNull('satisfaction_rating')
                ->groupBy('satisfaction_rating')
                ->selectRaw('satisfaction_rating, count(*) as count')
                ->pluck('count', 'satisfaction_rating'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Technical request statistics retrieved successfully',
        ]);
    }

    /**
     * Get current user's technical requests (for franchise owners/franchisor)
     */
    public function myRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get all franchises owned by this franchisor
        $franchiseIds = Franchise::where('franchisor_id', $user->id)->pluck('id');

        if ($franchiseIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                ],
                'message' => 'No franchises found for current user',
            ]);
        }

        $query = TechnicalRequest::whereIn('franchise_id', $franchiseIds)
            ->with(['requester', 'assignedUser', 'franchise', 'unit']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $requests = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => TechnicalRequestResource::collection($requests->items()),
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
            'message' => 'Technical requests retrieved successfully',
        ]);
    }

    /**
     * Get current user's unit technical requests (for unit managers)
     */
    public function myUnitRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('manager_id', $user->id)->first();

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
        }

        $requests = TechnicalRequest::where('unit_id', $unit->id)
            ->with(['franchise', 'unit', 'assignedTo', 'createdBy'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $requests,
            'message' => 'Unit technical requests retrieved successfully',
        ]);
    }

    /**
     * Get current user's assigned technical requests (for employees)
     */
    public function myAssignedRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        $requests = TechnicalRequest::where('assigned_to', $user->id)
            ->with(['franchise', 'unit', 'assignedTo', 'createdBy'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $requests,
            'message' => 'Assigned technical requests retrieved successfully',
        ]);
    }
}
