<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Unit;
use App\Models\UnitPerformance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitPerformanceController extends Controller
{
    /**
     * Get performance data for the franchisor's units
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user',
            ], 404);
        }

        $periodType = $request->get('period_type', 'monthly');
        $unitId = $request->get('unit_id'); // null for aggregated data

        // Calculate date range based on period type
        $startDate = $this->getStartDate($periodType);
        $endDate = now();

        // Build query
        $query = UnitPerformance::query()
            ->forFranchise($franchise->id)
            ->forPeriod($periodType)
            ->forUnit($unitId)
            ->inDateRange($startDate, $endDate)
            ->with(['unit']);

        $performances = $query->orderByDate()->get();

        return response()->json([
            'success' => true,
            'data' => $performances,
            'message' => 'Performance data retrieved successfully',
        ]);
    }

    /**
     * Get performance data formatted for charts
     */
    public function chartData(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user',
            ], 404);
        }

        $periodType = $request->get('period_type', 'monthly');
        $unitId = $request->get('unit_id'); // null for all units aggregated - used in individual unit data

        // Calculate date range based on period type
        $startDate = $this->getStartDate($periodType);
        $endDate = now();

        // Get all units for this franchise
        $units = Unit::where('franchise_id', $franchise->id)->get();

        // Prepare chart data structure
        $chartData = [
            'periods' => [],
            'datasets' => [
                'all' => ['revenue' => [], 'expenses' => [], 'royalties' => [], 'profit' => []],
            ],
        ];

        // Add unit-specific datasets
        foreach ($units as $unit) {
            $chartData['datasets'][$unit->id] = [
                'revenue' => [], 'expenses' => [], 'royalties' => [], 'profit' => [],
            ];
        }

        // Generate period labels
        $periods = $this->generatePeriodLabels($periodType, $startDate, $endDate);
        $chartData['periods'] = $periods;

        // Get performance data for each period
        foreach ($periods as $index => $periodLabel) {
            $periodDate = $this->getPeriodDate($periodType, $startDate, $index);

            // Get aggregated data for all units
            $aggregatedPerformance = UnitPerformance::query()
                ->forFranchise($franchise->id)
                ->forPeriod($periodType)
                ->whereNotNull('unit_id') // Only include specific units
                ->where('period_date', $periodDate->toDateString())
                ->get();

            $chartData['datasets']['all']['revenue'][] = $aggregatedPerformance->sum('revenue');
            $chartData['datasets']['all']['expenses'][] = $aggregatedPerformance->sum('expenses');
            $chartData['datasets']['all']['royalties'][] = $aggregatedPerformance->sum('royalties');
            $chartData['datasets']['all']['profit'][] = $aggregatedPerformance->sum('profit');

            // Get individual unit data
            foreach ($units as $unit) {
                $unitPerformance = $aggregatedPerformance->where('unit_id', $unit->id)->first();

                $chartData['datasets'][$unit->id]['revenue'][] = $unitPerformance ? $unitPerformance->revenue : 0;
                $chartData['datasets'][$unit->id]['expenses'][] = $unitPerformance ? $unitPerformance->expenses : 0;
                $chartData['datasets'][$unit->id]['royalties'][] = $unitPerformance ? $unitPerformance->royalties : 0;
                $chartData['datasets'][$unit->id]['profit'][] = $unitPerformance ? $unitPerformance->profit : 0;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $chartData,
            'message' => 'Chart data retrieved successfully',
        ]);
    }

    /**
     * Get top performing units
     */
    public function topPerformers(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user',
            ], 404);
        }

        $periodType = $request->get('period_type', 'monthly');
        $limit = $request->get('limit', 3);

        // Get most recent period
        $startDate = $this->getStartDate($periodType);
        $endDate = now();

        $topPerformers = UnitPerformance::query()
            ->forFranchise($franchise->id)
            ->forPeriod($periodType)
            ->whereNotNull('unit_id')
            ->inDateRange($startDate, $endDate)
            ->with(['unit'])
            ->orderByDate('desc')
            ->byRevenue('desc')
            ->limit($limit * 2) // Get more to calculate growth
            ->get()
            ->unique('unit_id')
            ->take($limit)
            ->map(function ($performance) {
                return [
                    'id' => $performance->unit->id,
                    'name' => $performance->unit->unit_name,
                    'location' => $performance->unit->city ?? 'Unknown',
                    'revenue' => number_format($performance->revenue, 2).' SAR',
                    'growth' => '+'.number_format($performance->growth_rate, 1).'%',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topPerformers,
            'message' => 'Top performers retrieved successfully',
        ]);
    }

    /**
     * Get customer satisfaction statistics
     */
    public function customerSatisfaction(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user',
            ], 404);
        }

        $periodType = $request->get('period_type', 'monthly');

        // Get most recent period
        $startDate = $this->getStartDate($periodType);
        $endDate = now();

        $performances = UnitPerformance::query()
            ->forFranchise($franchise->id)
            ->forPeriod($periodType)
            ->whereNotNull('unit_id')
            ->inDateRange($startDate, $endDate)
            ->get();

        if ($performances->isEmpty()) {
            $satisfactionData = [
                'score' => 0,
                'max_score' => 5.0,
                'total_reviews' => 0,
                'trend' => '0',
            ];
        } else {
            $currentPeriod = $performances->last();
            $previousPeriod = $performances->count() > 1 ? $performances[$performances->count() - 2] : null;

            $currentScore = $currentPeriod->customer_rating;
            $previousScore = $previousPeriod ? $previousPeriod->customer_rating : $currentScore;
            $trend = $currentScore - $previousScore;

            $satisfactionData = [
                'score' => $currentScore,
                'max_score' => 5.0,
                'total_reviews' => $performances->sum('customer_reviews_count'),
                'trend' => ($trend >= 0 ? '+' : '').number_format($trend, 1),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $satisfactionData,
            'message' => 'Customer satisfaction data retrieved successfully',
        ]);
    }

    /**
     * Get top and lowest rated units
     */
    public function ratings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user',
            ], 404);
        }

        $periodType = $request->get('period_type', 'monthly');

        // Get most recent period
        $startDate = $this->getStartDate($periodType);
        $endDate = now();

        $recentPerformances = UnitPerformance::query()
            ->forFranchise($franchise->id)
            ->forPeriod($periodType)
            ->whereNotNull('unit_id')
            ->inDateRange($startDate, $endDate)
            ->with(['unit'])
            ->orderByDate('desc')
            ->byRating('desc')
            ->get();

        $topRated = $recentPerformances->first();
        $lowestRated = $recentPerformances->last();

        $ratingsData = [
            'top_rated' => $topRated ? [
                'id' => $topRated->unit->id,
                'name' => $topRated->unit->unit_name,
                'location' => $topRated->unit->city ?? 'Unknown',
                'rating' => $topRated->customer_rating,
                'reviews' => $topRated->customer_reviews_count,
                'manager' => $topRated->unit->franchisee?->name ?? 'Unknown',
            ] : null,
            'lowest_rated' => $lowestRated && $lowestRated->id !== $topRated?->id ? [
                'id' => $lowestRated->unit->id,
                'name' => $lowestRated->unit->unit_name,
                'location' => $lowestRated->unit->city ?? 'Unknown',
                'rating' => $lowestRated->customer_rating,
                'reviews' => $lowestRated->customer_reviews_count,
                'manager' => $lowestRated->unit->franchisee?->name ?? 'Unknown',
            ] : null,
        ];

        return response()->json([
            'success' => true,
            'data' => $ratingsData,
            'message' => 'Ratings data retrieved successfully',
        ]);
    }

    /**
     * Export performance data
     */
    public function export(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user',
            ], 404);
        }

        $periodType = $request->get('period_type', 'monthly');
        $unitId = $request->get('unit_id'); // null for all units
        $exportType = $request->get('export_type', 'performance'); // performance, stats, all

        // Calculate date range
        $startDate = $this->getStartDate($periodType);
        $endDate = now();

        $exportData = [];

        if ($exportType === 'performance' || $exportType === 'all') {
            $performances = UnitPerformance::query()
                ->forFranchise($franchise->id)
                ->forPeriod($periodType)
                ->forUnit($unitId)
                ->inDateRange($startDate, $endDate)
                ->with(['unit'])
                ->orderByDate()
                ->get();

            $exportData['performance'] = $performances->map(function ($performance) {
                return [
                    'period_date' => $performance->period_date->format('Y-m-d'),
                    'unit_name' => $performance->unit?->unit_name ?? 'All Units',
                    'revenue' => $performance->revenue,
                    'expenses' => $performance->expenses,
                    'royalties' => $performance->royalties,
                    'profit' => $performance->profit,
                    'profit_margin' => $performance->profit_margin,
                    'customer_rating' => $performance->customer_rating,
                    'customer_reviews' => $performance->customer_reviews_count,
                    'growth_rate' => $performance->growth_rate,
                ];
            });
        }

        if ($exportType === 'stats' || $exportType === 'all') {
            // Add statistics summary data
            $stats = $this->generateStats($franchise->id, $periodType, $startDate, $endDate);
            $exportData['stats'] = $stats;
        }

        return response()->json([
            'success' => true,
            'data' => $exportData,
            'message' => 'Export data retrieved successfully',
        ]);
    }

    /**
     * Get available units for the franchisor
     */
    public function units(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return response()->json([
                'success' => false,
                'message' => 'No franchise found for current user',
            ], 404);
        }

        $units = Unit::where('franchise_id', $franchise->id)
            ->select('id', 'unit_name', 'city', 'unit_code')
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->unit_name,
                    'location' => $unit->city ?? 'Unknown Location',
                    'code' => $unit->unit_code,
                ];
            });

        // Add "All Units" option at the beginning
        $allUnits = [
            [
                'id' => 'all',
                'name' => 'All Units',
                'location' => 'Overview',
                'code' => 'ALL',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => collect($allUnits)->concat($units),
            'message' => 'Units retrieved successfully',
        ]);
    }

    // Helper methods

    private function getStartDate(string $periodType): Carbon
    {
        return match ($periodType) {
            'daily' => now()->subDays(14), // Last 14 days
            'monthly' => now()->subMonths(12), // Last 12 months
            'yearly' => now()->subYears(5), // Last 5 years
            default => now()->subMonths(12),
        };
    }

    private function generatePeriodLabels(string $periodType, Carbon $startDate, Carbon $endDate): array
    {
        $labels = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $labels[] = match ($periodType) {
                'daily' => $current->format('M d'),
                'monthly' => $current->format('M'),
                'yearly' => $current->format('Y'),
                default => $current->format('M'),
            };

            $current->add(match ($periodType) {
                'daily' => '1 day',
                'monthly' => '1 month',
                'yearly' => '1 year',
                default => '1 month',
            });
        }

        return $labels;
    }

    private function getPeriodDate(string $periodType, Carbon $startDate, int $index): Carbon
    {
        return match ($periodType) {
            'daily' => $startDate->copy()->addDays($index),
            'monthly' => $startDate->copy()->addMonths($index),
            'yearly' => $startDate->copy()->addYears($index),
            default => $startDate->copy()->addMonths($index),
        };
    }

    private function generateStats(int $franchiseId, string $periodType, Carbon $startDate, Carbon $endDate): array
    {
        $performances = UnitPerformance::query()
            ->forFranchise($franchiseId)
            ->forPeriod($periodType)
            ->inDateRange($startDate, $endDate)
            ->get();

        return [
            'total_revenue' => $performances->sum('revenue'),
            'total_expenses' => $performances->sum('expenses'),
            'total_profit' => $performances->sum('profit'),
            'average_rating' => $performances->avg('customer_rating'),
            'total_reviews' => $performances->sum('customer_reviews_count'),
            'total_transactions' => $performances->sum('total_transactions'),
            'period_type' => $periodType,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];
    }
}
