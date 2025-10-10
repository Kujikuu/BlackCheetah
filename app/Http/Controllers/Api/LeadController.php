<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::with(['franchise', 'assignedUser']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('source')) {
            $query->where('lead_source', $request->source);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $leads = $query->paginate($perPage);

        // Transform the data to match frontend expectations
        $transformedLeads = $leads->getCollection()->map(function ($lead) {
            return [
                'id' => $lead->id,
                'firstName' => $lead->first_name,
                'lastName' => $lead->last_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company' => $lead->company_name,
                'country' => $lead->country,
                'state' => $lead->address, // Using address as state for now
                'city' => $lead->city,
                'source' => $lead->lead_source,
                'status' => $lead->status,
                'owner' => $lead->assignedUser ? $lead->assignedUser->name : 'Unassigned',
                'lastContacted' => $lead->last_contact_date ? $lead->last_contact_date->format('Y-m-d') : 'Never',
                'priority' => $lead->priority,
                'estimatedInvestment' => $lead->estimated_investment,
                'franchiseFeeQuoted' => $lead->franchise_fee_quoted,
                'notes' => $lead->notes,
                'expectedDecisionDate' => $lead->expected_decision_date,
                'nextFollowUpDate' => $lead->next_follow_up_date,
                'contactAttempts' => $lead->contact_attempts,
                'interests' => $lead->interests,
                'documents' => $lead->documents,
                'communicationLog' => $lead->communication_log,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedLeads,
            'total' => $leads->total(),
            'current_page' => $leads->currentPage(),
            'per_page' => $leads->perPage(),
            'last_page' => $leads->lastPage(),
            'message' => 'Leads retrieved successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:leads,email',
            'phone' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'source' => 'required|in:website,referral,social_media,advertisement,cold_call,event,other',
            'status' => 'required|in:new,contacted,qualified,proposal_sent,negotiating,closed_won,closed_lost',
            'priority' => 'required|in:low,medium,high,urgent',
            'franchise_id' => 'nullable|exists:franchises,id',
            'assigned_to' => 'nullable|exists:users,id',
            'interests' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Get the current user's franchise
        $user = Auth::user();
        $franchise = \App\Models\Franchise::where('franchisor_id', $user->id)->first();

        if (!$franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for this user',
            ], 404);
        }

        // Map fields to match database schema
        $validated['lead_source'] = $validated['source'];
        $validated['company_name'] = $validated['company'];
        $validated['franchise_id'] = $franchise->id;
        unset($validated['source'], $validated['company']);

        $lead = Lead::create($validated);

        return response()->json([
            'success' => true,
            'data' => $lead->load(['franchise', 'assignedUser']),
            'message' => 'Lead created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead): JsonResponse
    {
        $lead->load(['franchise', 'assignedUser']);

        return response()->json([
            'success' => true,
            'data' => $lead,
            'message' => 'Lead retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'source' => 'sometimes|in:website,referral,social_media,email_campaign,phone_call,trade_show,advertisement,other',
            'status' => 'sometimes|in:new,contacted,qualified,proposal_sent,negotiating,converted,lost,unqualified',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'franchise_id' => 'nullable|exists:franchises,id',
            'assigned_to' => 'nullable|exists:users,id',
            'budget_range' => 'nullable|string|max:100',
            'timeline' => 'nullable|string|max:100',
            'interests' => 'nullable|array',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        // Map source to lead_source for database storage if source is provided
        if (isset($validated['source'])) {
            $validated['lead_source'] = $validated['source'];
            unset($validated['source']);
        }

        $lead->update($validated);

        return response()->json([
            'success' => true,
            'data' => $lead->load(['franchise', 'assignedUser']),
            'message' => 'Lead updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully',
        ]);
    }

    /**
     * Convert lead to customer/franchise
     */
    public function convert(Lead $lead): JsonResponse
    {
        $lead->update([
            'status' => 'converted',
            'converted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $lead,
            'message' => 'Lead converted successfully',
        ]);
    }

    /**
     * Mark lead as lost
     */
    public function markAsLost(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'lost_reason' => 'required|string|max:255',
        ]);

        $lead->update([
            'status' => 'lost',
            'lost_reason' => $validated['lost_reason'],
            'lost_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $lead,
            'message' => 'Lead marked as lost',
        ]);
    }

    /**
     * Assign lead to user
     */
    public function assign(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $lead->update($validated);

        return response()->json([
            'success' => true,
            'data' => $lead->load(['assignedUser']),
            'message' => 'Lead assigned successfully',
        ]);
    }

    /**
     * Add note to lead
     */
    public function addNote(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        $currentNotes = $lead->notes ?? '';
        $newNote = '['.now()->format('Y-m-d H:i:s').'] '.$validated['note'];
        $updatedNotes = $currentNotes ? $currentNotes."\n\n".$newNote : $newNote;

        $lead->update(['notes' => $updatedNotes]);

        return response()->json([
            'success' => true,
            'data' => $lead,
            'message' => 'Note added successfully',
        ]);
    }

    /**
     * Get leads for the authenticated franchisor
     */
    public function myLeads(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = \App\Models\Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $query = Lead::where('franchise_id', $franchise->id)
                ->with(['assignedUser:id,name,email']);

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply source filter
            if ($request->has('source') && $request->source) {
                $query->where('lead_source', $request->source);
            }

            // Apply owner filter (assigned_to)
            if ($request->has('owner') && $request->owner) {
                $query->where('assigned_to', $request->owner);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $leads = $query->paginate($perPage);

            // Transform the data to match frontend expectations
            $leads->getCollection()->transform(function ($lead) {
                return [
                    'id' => $lead->id,
                    'firstName' => $lead->first_name,
                    'lastName' => $lead->last_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'company' => $lead->company_name,
                    'country' => $lead->country,
                    'state' => $lead->state ?? '',
                    'city' => $lead->city,
                    'source' => $lead->lead_source,
                    'status' => $lead->status,
                    'owner' => $lead->assignedUser ? $lead->assignedUser->name : 'Unassigned',
                    'lastContacted' => $lead->last_contact_date ? $lead->last_contact_date->format('Y-m-d') : null,
                    'scheduledMeeting' => $lead->next_follow_up_date ? $lead->next_follow_up_date->format('Y-m-d') : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $leads->items(),
                'total' => $leads->total(),
                'per_page' => $leads->perPage(),
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leads',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Lead::query();

        // Apply franchise filter if provided
        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        $stats = [
            'total_leads' => $query->count(),
            'new_leads' => $query->where('status', 'new')->count(),
            'qualified_leads' => $query->where('status', 'qualified')->count(),
            'converted_leads' => $query->where('status', 'converted')->count(),
            'lost_leads' => $query->where('status', 'lost')->count(),
            'conversion_rate' => $query->count() > 0 ?
                round(($query->where('status', 'converted')->count() / $query->count()) * 100, 2) : 0,
            'leads_by_source' => $query->groupBy('lead_source')
                ->selectRaw('lead_source, count(*) as count')
                ->pluck('count', 'lead_source'),
            'leads_by_priority' => $query->groupBy('priority')
                ->selectRaw('priority, count(*) as count')
                ->pluck('count', 'priority'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Lead statistics retrieved successfully',
        ]);
    }
}
