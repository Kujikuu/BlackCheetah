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
     * Get franchise ID for the current user based on their role
     */
    private function getUserFranchiseId($user): ?int
    {
        if ($user->hasRole('admin')) {
            // Admins can see all, no franchise restriction
            return null;
        } elseif ($user->hasRole('franchisor')) {
            // Franchisors own franchises
            $franchise = \App\Models\Franchise::where('franchisor_id', $user->id)->first();

            return $franchise?->id;
        } else {
            // All other roles use franchise_id from users table
            return $user->franchise_id;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $franchiseId = $this->getUserFranchiseId($user);

        // If not admin and no franchise found, return error
        if (! $user->hasRole('admin') && ! $franchiseId) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for this user',
            ], 404);
        }

        $query = Lead::with(['franchise', 'assignedUser']);

        // Scope to user's franchise (unless admin)
        if ($franchiseId) {
            $query->where('franchise_id', $franchiseId);
        }

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('source') && $request->source) {
            $query->where('lead_source', $request->source);
        }

        if ($request->has('owner') && $request->owner) {
            $query->whereHas('assignedUser', function ($q) use ($request) {
                $q->where('name', 'like', '%'.str_replace('_', ' ', $request->owner).'%');
            });
        }

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

        // Apply sorting
        $sortBy = $request->get('sortBy', 'created_at');
        $sortOrder = $request->get('orderBy', 'desc');

        // Map frontend field names to database columns
        $sortMapping = [
            'name' => 'first_name',
            'company' => 'company_name',
            'lastContacted' => 'last_contact_date',
        ];

        $sortColumn = $sortMapping[$sortBy] ?? $sortBy;
        $query->orderBy($sortColumn, $sortOrder);

        // Pagination
        $perPage = $request->get('itemsPerPage', 10);
        if ($perPage == -1) {
            $perPage = $query->count();
        }

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
                'state' => $lead->address,
                'city' => $lead->city,
                'source' => ucfirst(str_replace('_', ' ', $lead->lead_source)),
                'status' => $lead->status,
                'owner' => $lead->assignedUser ? $lead->assignedUser->name : 'Unassigned',
                'lastContacted' => $lead->last_contact_date ? $lead->last_contact_date->format('Y-m-d') : null,
                'scheduledMeeting' => $lead->next_follow_up_date ? $lead->next_follow_up_date->format('Y-m-d') : null,
            ];
        });

        return response()->json([
            'success' => true,
            'leads' => $transformedLeads,
            'total' => $leads->total(),
            'currentPage' => $leads->currentPage(),
            'perPage' => $leads->perPage(),
            'lastPage' => $leads->lastPage(),
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
        $franchiseId = $this->getUserFranchiseId($user);

        if (! $franchiseId) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for this user',
            ], 404);
        }

        // Map fields to match database schema
        $validated['lead_source'] = $validated['source'];
        $validated['company_name'] = $validated['company'];
        $validated['franchise_id'] = $franchiseId;
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
            $franchiseId = $this->getUserFranchiseId($user);

            if (! $franchiseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $query = Lead::where('franchise_id', $franchiseId)
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
        $user = Auth::user();
        $franchiseId = $this->getUserFranchiseId($user);

        $query = Lead::query();

        // Scope to user's franchise (unless admin)
        if ($franchiseId) {
            $query->where('franchise_id', $franchiseId);
        }

        $totalLeads = (clone $query)->count();
        $qualifiedLeads = (clone $query)->where('status', 'qualified')->count();
        $unqualifiedLeads = (clone $query)->where('status', 'unqualified')->count();

        // Calculate month-over-month changes
        $lastMonthTotal = (clone $query)->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $currentMonthTotal = (clone $query)->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthQualified = (clone $query)->where('status', 'qualified')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $currentMonthQualified = (clone $query)->where('status', 'qualified')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthUnqualified = (clone $query)->where('status', 'unqualified')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $currentMonthUnqualified = (clone $query)->where('status', 'unqualified')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Calculate percentage changes
        $totalChange = $lastMonthTotal > 0 ?
            round((($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 0) :
            ($currentMonthTotal > 0 ? 100 : 0);

        $qualifiedChange = $lastMonthQualified > 0 ?
            round((($currentMonthQualified - $lastMonthQualified) / $lastMonthQualified) * 100, 0) :
            ($currentMonthQualified > 0 ? 100 : 0);

        $unqualifiedChange = $lastMonthUnqualified > 0 ?
            round((($currentMonthUnqualified - $lastMonthUnqualified) / $lastMonthUnqualified) * 100, 0) :
            ($currentMonthUnqualified > 0 ? 100 : 0);

        $stats = [
            [
                'title' => 'Total Leads',
                'value' => number_format($totalLeads),
                'change' => (int) $totalChange,
                'icon' => 'tabler-users',
                'iconColor' => 'primary',
            ],
            [
                'title' => 'Qualified',
                'value' => number_format($qualifiedLeads),
                'change' => (int) $qualifiedChange,
                'icon' => 'tabler-user-check',
                'iconColor' => 'success',
            ],
            [
                'title' => 'Unqualified',
                'value' => number_format($unqualifiedLeads),
                'change' => (int) $unqualifiedChange,
                'icon' => 'tabler-user-x',
                'iconColor' => 'error',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Lead statistics retrieved successfully',
        ]);
    }

    /**
     * Bulk delete leads
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:leads,id',
        ]);

        $user = Auth::user();
        $franchiseId = $this->getUserFranchiseId($user);

        $query = Lead::whereIn('id', $validated['ids']);

        // Scope to user's franchise (unless admin)
        if ($franchiseId) {
            $query->where('franchise_id', $franchiseId);
        }

        $deleted = $query->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} lead(s) deleted successfully",
        ]);
    }

    /**
     * Import leads from CSV
     */
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $user = Auth::user();
        $franchiseId = $this->getUserFranchiseId($user);

        if (! $franchiseId) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for this user',
            ], 404);
        }

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Read header row
        $header = fgetcsv($handle);

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $data = array_combine($header, $row);

                Lead::create([
                    'franchise_id' => $franchiseId,
                    'first_name' => $data['First Name'] ?? '',
                    'last_name' => $data['Last Name'] ?? '',
                    'email' => $data['Email'] ?? '',
                    'phone' => $data['Phone'] ?? '',
                    'company_name' => $data['Company'] ?? null,
                    'country' => $data['Country'] ?? null,
                    'address' => $data['State'] ?? null,
                    'city' => $data['City'] ?? null,
                    'lead_source' => strtolower(str_replace(' ', '_', $data['Lead Source'] ?? 'other')),
                    'status' => strtolower($data['Lead Status'] ?? 'new'),
                    'assigned_to' => $this->findUserByName($data['Lead Owner'] ?? null),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$imported}: ".$e->getMessage();
            }
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'message' => "{$imported} lead(s) imported successfully",
            'errors' => $errors,
        ]);
    }

    /**
     * Export leads to CSV
     */
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = Auth::user();
        $franchiseId = $this->getUserFranchiseId($user);

        $query = Lead::with(['assignedUser']);

        // Scope to user's franchise (unless admin)
        if (! $franchiseId && ! $user->hasRole('admin')) {
            abort(404, 'No franchise found for this user');
        }

        if ($franchiseId) {
            $query->where('franchise_id', $franchiseId);
        }

        // Filter by selected IDs if provided
        if ($request->has('ids') && is_array($request->ids) && count($request->ids) > 0) {
            $query->whereIn('id', $request->ids);
        }

        $leads = $query->get();

        $fileName = 'leads_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');

            // Write header
            fputcsv($handle, [
                'First Name',
                'Last Name',
                'Company',
                'Email',
                'Phone',
                'City',
                'State',
                'Source',
                'Status',
                'Owner',
                'Last Contacted',
            ]);

            // Write data
            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->first_name,
                    $lead->last_name,
                    $lead->company_name,
                    $lead->email,
                    $lead->phone,
                    $lead->city,
                    $lead->address,
                    $lead->lead_source,
                    $lead->status,
                    $lead->assignedUser ? $lead->assignedUser->name : 'Unassigned',
                    $lead->last_contact_date ? $lead->last_contact_date->format('Y-m-d') : '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Helper method to find user by name
     */
    private function findUserByName(?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        $user = \App\Models\User::where('name', 'like', "%{$name}%")->first();

        return $user ? $user->id : null;
    }
}
