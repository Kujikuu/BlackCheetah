<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Http\Requests\StoreTechnicalRequestRequest;
use App\Http\Requests\UpdateTechnicalRequestRequest;
use App\Http\Resources\TechnicalRequestResource;
use App\Models\TechnicalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechnicalRequestController extends BaseResourceController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = TechnicalRequest::with(['requester']);

        // Apply role-based filtering
        switch ($user->role) {
            case 'admin':
                // Admin can see all requests - no additional filtering needed
                break;

            default:
                // For all other roles, show only requests they created
                $query->where('requester_id', $user->id);
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

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sort = $this->parseSortParams($request, 'created_at');
        $query->orderBy($sort['column'], $sort['order']);

        // Pagination
        $perPage = $this->getPaginationParams($request);
        $requests = $query->paginate($perPage);

        return $this->successResponse($requests, 'Technical requests retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTechnicalRequestRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            // Handle both array and single file cases
            if (is_array($files)) {
                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('technical-requests/attachments', $fileName, 'public');
                        $attachmentPaths[] = asset('storage/' . $path);
                    }
                }
            } elseif ($files && $files->isValid()) {
                $fileName = time() . '_' . $files->getClientOriginalName();
                $path = $files->storeAs('technical-requests/attachments', $fileName, 'public');
                $attachmentPaths[] = asset('storage/' . $path);
            }
        }

        // Remove attachments from validated data as we'll set it manually
        unset($validated['attachments']);
        $validated['attachments'] = $attachmentPaths;

        $technicalRequest = TechnicalRequest::create($validated);

        return $this->successResponse(
            new TechnicalRequestResource($technicalRequest->load(['requester'])),
            'Technical request created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(TechnicalRequest $technicalRequest): JsonResponse
    {
        $technicalRequest->load(['requester']);

        return $this->successResponse(new TechnicalRequestResource($technicalRequest), 'Technical request retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTechnicalRequestRequest $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $validated = $request->validated();

        $technicalRequest->update($validated);

        return $this->successResponse(
            new TechnicalRequestResource($technicalRequest->load(['requester'])),
            'Technical request updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TechnicalRequest $technicalRequest): JsonResponse
    {
        $technicalRequest->delete();

        return $this->successResponse(null, 'Technical request deleted successfully');
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

        return $this->successResponse(['count' => $count], "{$count} technical request(s) deleted successfully");
    }


    /**
     * Update technical request status
     */
    public function updateStatus(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,pending_info,resolved,closed,cancelled',
            'message' => 'nullable|string',
        ]);

        $technicalRequest->updateStatus($validated['status']);

        return $this->successResponse(new TechnicalRequestResource($technicalRequest->load(['requester'])), 'Technical request status updated successfully');
    }

    /**
     * Resolve technical request
     */
    public function resolve(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $technicalRequest->updateStatus('resolved');

        return $this->successResponse(new TechnicalRequestResource($technicalRequest->load(['requester'])), 'Technical request resolved successfully');
    }

    /**
     * Close technical request
     */
    public function close(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $technicalRequest->updateStatus('closed');

        return $this->successResponse(new TechnicalRequestResource($technicalRequest->load(['requester'])), 'Technical request closed successfully');
    }

    /**
     * Add attachment to technical request
     */
    public function addAttachment(Request $request, TechnicalRequest $technicalRequest): JsonResponse
    {
        $validated = $request->validate([
            'attachment_url' => 'required|string|max:500',
        ]);

        $technicalRequest->addAttachment($validated['attachment_url']);

        return $this->successResponse(new TechnicalRequestResource($technicalRequest->load(['requester'])), 'Attachment added successfully');
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

            default:
                // For all other roles, show only requests they created
                $query->where('requester_id', $user->id);
        }

        $stats = [
            'total_requests' => $query->count(),
            'open_requests' => $query->open()->count(),
            'resolved_requests' => $query->resolved()->count(),
            'high_priority_requests' => $query->highPriority()->count(),
            'requests_by_category' => $query->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->pluck('count', 'category'),
            'requests_by_priority' => $query->groupBy('priority')
                ->selectRaw('priority, count(*) as count')
                ->pluck('count', 'priority'),
            'requests_by_status' => $query->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status'),
        ];

        return $this->successResponse($stats, 'Technical request statistics retrieved successfully');
    }

    /**
     * Get current user's technical requests
     */
    public function myRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = TechnicalRequest::where('requester_id', $user->id)
            ->with(['requester']);

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

        return $this->successResponse([
            'data' => TechnicalRequestResource::collection($requests->items()),
            'current_page' => $requests->currentPage(),
            'last_page' => $requests->lastPage(),
            'per_page' => $requests->perPage(),
            'total' => $requests->total(),
        ], 'Technical requests retrieved successfully');
    }

    /**
     * Get franchisee's unit technical requests (alias for myRequests for franchisee route compatibility)
     */
    public function myUnitRequests(Request $request): JsonResponse
    {
        return $this->myRequests($request);
    }
}
