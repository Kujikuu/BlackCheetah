<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Requests\MarkLeadAsLostRequest;
use App\Http\Requests\AssignLeadRequest;
use App\Http\Requests\AddLeadNoteRequest;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends BaseResourceController
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
        if (!$user->hasRole('admin') && !$franchiseId) {
            return $this->notFoundResponse('No franchise found for this user');
        }

        $query = Lead::with(['franchise', 'assignedUser']);

        // Scope to user's franchise (unless admin)
        if ($franchiseId) {
            $query->where('franchise_id', $franchiseId);
        }

        // Sales users should only see their assigned leads
        if ($user->hasRole('broker')) {
            $query->where('assigned_to', $user->id);
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
                $q->where('name', 'like', '%' . str_replace('_', ' ', $request->owner) . '%');
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
                'company' => $lead->company_name ?? '',
                'nationality' => $lead->nationality,
                'state' => $lead->address ?? '',
                'city' => $lead->city,
                'source' => $lead->lead_source, // Keep original value for consistency
                'status' => $lead->status,
                'owner' => $lead->assignedUser ? $lead->assignedUser->name : 'Unassigned',
                'lastContacted' => $lead->last_contact_date ? $lead->last_contact_date->format('Y-m-d') : null,
                'scheduledMeeting' => $lead->next_follow_up_date ? $lead->next_follow_up_date->format('Y-m-d') : null,
            ];
        });

        return $this->successResponse([
            'leads' => $transformedLeads,
            'total' => $leads->total(),
            'currentPage' => $leads->currentPage(),
            'perPage' => $leads->perPage(),
            'lastPage' => $leads->lastPage(),
        ], 'Leads retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Get the current user (broker)
        $user = Auth::user();
        $franchiseId = $this->getUserFranchiseId($user);

        if (!$franchiseId) {
            return $this->notFoundResponse('No franchise found for this user');
        }

        // Automatically assign the lead to the broker who created it
        $validated['franchise_id'] = $franchiseId;
        $validated['assigned_to'] = $user->id;
        
        $lead = Lead::create($validated);

        return $this->successResponse(
            $lead->load(['franchise', 'assignedUser']),
            'Lead created successfully and assigned to you',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead): JsonResponse
    {
        $lead->load(['franchise', 'assignedUser']);

        return $this->successResponse($lead, 'Lead retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        $validated = $request->validated();

        // Map source to lead_source for database storage if source is provided
        if (isset($validated['source'])) {
            $validated['lead_source'] = $validated['source'];
            unset($validated['source']);
        }

        $lead->update($validated);

        return $this->successResponse($lead->load(['franchise', 'assignedUser']), 'Lead updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return $this->successResponse(null, 'Lead deleted successfully');
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

        return $this->successResponse($lead, 'Lead converted successfully');
    }

    /**
     * Mark lead as lost
     */
    public function markAsLost(MarkLeadAsLostRequest $request, Lead $lead): JsonResponse
    {
        $validated = $request->validated();

        $lead->update([
            'status' => 'lost',
            'lost_reason' => $validated['lost_reason'],
            'lost_at' => now(),
        ]);

        return $this->successResponse($lead, 'Lead marked as lost');
    }

    /**
     * Assign lead to user
     */
    public function assign(AssignLeadRequest $request, Lead $lead): JsonResponse
    {
        $validated = $request->validated();

        $lead->update($validated);

        return $this->successResponse($lead->load(['assignedUser']), 'Lead assigned successfully');
    }

    /**
     * Add note to lead
     */
    public function addNote(AddLeadNoteRequest $request, Lead $lead): JsonResponse
    {
        $validated = $request->validated();

        $currentNotes = $lead->notes ?? '';
        $newNote = '[' . now()->format('Y-m-d H:i:s') . '] ' . $validated['note'];
        $updatedNotes = $currentNotes ? $currentNotes . "\n\n" . $newNote : $newNote;

        $lead->update(['notes' => $updatedNotes]);

        return $this->successResponse($lead, 'Note added successfully');
    }

    /**
     * Get leads for the authenticated franchisor
     */
    public function myLeads(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            // $franchiseId = $this->getUserFranchiseId($user);

            if (!$user->hasRole('broker')) {
                return $this->notFoundResponse('You are not authorized to access this resource');
            }

            $query = Lead::with(['assignedUser:id,name,email']);

            // Filter by franchise
            // if ($franchiseId) {
            //     $query->where('franchise_id', $franchiseId);
            // }

            // Sales users should only see their assigned leads
            if ($user->hasRole('broker')) {
                $query->where('assigned_to', $user->id);
            }

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
            $leads->getCollection()->transform(function ($lead) {
                return [
                    'id' => $lead->id,
                    'firstName' => $lead->first_name,
                    'lastName' => $lead->last_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'company' => $lead->company_name,
                    'nationality' => $lead->nationality,
                    'state' => $lead->state ?? '',
                    'city' => $lead->city,
                    'source' => $lead->lead_source,
                    'status' => $lead->status,
                    'owner' => $lead->assignedUser ? $lead->assignedUser->name : 'Unassigned',
                    'lastContacted' => $lead->last_contact_date ? $lead->last_contact_date->format('Y-m-d') : null,
                    'scheduledMeeting' => $lead->next_follow_up_date ? $lead->next_follow_up_date->format('Y-m-d') : null,
                ];
            });

            return $this->successResponse([
                'leads' => $leads->items(),
                'total' => $leads->total(),
                'perPage' => $leads->perPage(),
                'currentPage' => $leads->currentPage(),
                'lastPage' => $leads->lastPage(),
            ], 'Leads retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch leads', $e->getMessage(), 500);
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

        // Sales users should only see their assigned leads statistics
        if ($user->hasRole('broker')) {
            $query->where('assigned_to', $user->id);
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

        return $this->successResponse($stats, 'Lead statistics retrieved successfully');
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

        return $this->successResponse(null, "{$deleted} lead(s) deleted successfully");
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

        if (!$franchiseId) {
            return $this->notFoundResponse('No franchise found for this user');
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
                    'nationality' => $data['Nationality'] ?? null,
                    'address' => $data['State'] ?? null,
                    'city' => $data['City'] ?? null,
                    'lead_source' => strtolower(str_replace(' ', '_', $data['Lead Source'] ?? 'other')),
                    'status' => strtolower($data['Lead Status'] ?? 'new'),
                    'assigned_to' => $this->findUserByName($data['Lead Owner'] ?? null),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$imported}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return $this->successResponse([
            'imported' => $imported,
            'errors' => $errors,
        ], "{$imported} lead(s) imported successfully");
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
        if (!$franchiseId && !$user->hasRole('admin')) {
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

        $fileName = 'leads_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');

            // Write header
            fputcsv($handle, [
                'First Name',
                'Last Name',
                'Company',
                'Email',
                'Phone',
                'Nationality',
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
                    $lead->nationality,
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
        if (!$name) {
            return null;
        }

        $user = \App\Models\User::where('name', 'like', "%{$name}%")->first();

        return $user ? $user->id : null;
    }
}
