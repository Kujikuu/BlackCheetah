<?php

namespace App\Http\Controllers\Api\V1\Franchisor;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Models\Franchise;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Franchisor Lead Management Controller
 * 
 * Handles lead analytics and franchisee management for franchisors
 */
class LeadManagementController extends BaseResourceController
{
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
            $perPage = $this->getPaginationParams($request);
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
            ], 'Leads data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch leads data', 500, $e->getMessage());
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
            $perPage = $this->getPaginationParams($request);
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
                    'country' => $franchisee->country,
                    'created_at' => $franchisee->created_at,
                ];
            });

            return $this->successResponse($franchisees, 'Franchisees retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch franchisees', 500, $e->getMessage());
        }
    }
}
