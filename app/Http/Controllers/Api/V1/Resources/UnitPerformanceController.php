<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Models\Franchise;
use App\Models\Unit;
use App\Models\UnitPerformance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitPerformanceController extends BaseResourceController
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
            return $this->notFoundResponse('No franchise found for current user');
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

        return $this->successResponse($performances, 'Performance data retrieved successfully');
    }

    /**
     * Get performance data formatted for charts
     * Calculates metrics in real-time from source tables
     */
    public function chartData(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return $this->notFoundResponse('No franchise found for current user');
        }

        $periodType = $request->get('period_type', 'monthly');
        $unitId = $request->get('unit_id'); // null for all units aggregated

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
                'revenue' => [],
                'expenses' => [],
                'royalties' => [],
                'profit' => [],
            ];
        }

        // Generate period labels
        $periods = $this->generatePeriodLabels($periodType, $startDate, $endDate);
        $chartData['periods'] = $periods;

        // Get performance data for each period
        foreach ($periods as $index => $periodLabel) {
            $periodDate = $this->getPeriodDate($periodType, $startDate, $index);

            // Calculate metrics from source tables for this period
            [$year, $month] = $this->extractYearMonth($periodDate, $periodType);

            // Get aggregated data for all units
            $allRevenue = 0;
            $allExpenses = 0;
            $allRoyalties = 0;

            foreach ($units as $unit) {
                // Calculate revenue for this unit and period
                $revenue = $this->calculateRevenue($unit->id, $year, $month, $periodType, $periodDate);
                $expenses = $this->calculateExpenses($unit->id, $year, $month, $periodType, $periodDate);
                $royalties = $this->calculateRoyalties($unit->id, $year, $month, $periodType, $periodDate);
                $profit = $revenue - $expenses;

                // Add to aggregated totals
                $allRevenue += $revenue;
                $allExpenses += $expenses;
                $allRoyalties += $royalties;

                // Store unit-specific data
                $chartData['datasets'][$unit->id]['revenue'][] = $revenue;
                $chartData['datasets'][$unit->id]['expenses'][] = $expenses;
                $chartData['datasets'][$unit->id]['royalties'][] = $royalties;
                $chartData['datasets'][$unit->id]['profit'][] = $profit;
            }

            // Store aggregated data
            $chartData['datasets']['all']['revenue'][] = $allRevenue;
            $chartData['datasets']['all']['expenses'][] = $allExpenses;
            $chartData['datasets']['all']['royalties'][] = $allRoyalties;
            $chartData['datasets']['all']['profit'][] = $allRevenue - $allExpenses;
        }

        return $this->successResponse($chartData, 'Chart data retrieved successfully');
    }

    /**
     * Get top performing units - calculated in real-time
     */
    public function topPerformers(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return $this->notFoundResponse('No franchise found for current user');
        }

        $periodType = $request->get('period_type', 'monthly');
        $limit = $request->get('limit', 3);

        // Get current and previous period dates
        $currentDate = now();
        [$currentYear, $currentMonth] = [$currentDate->year, $currentDate->month];

        $previousDate = $currentDate->copy()->subMonth();
        [$previousYear, $previousMonth] = [$previousDate->year, $previousDate->month];

        // Get all units for this franchise
        $units = Unit::where('franchise_id', $franchise->id)->get();

        $performanceData = [];

        foreach ($units as $unit) {
            // Calculate current period revenue
            $currentRevenue = $this->calculateRevenue($unit->id, $currentYear, $currentMonth, $periodType, $currentDate);

            // Calculate previous period revenue for growth rate
            $previousRevenue = $this->calculateRevenue($unit->id, $previousYear, $previousMonth, $periodType, $previousDate);

            // Calculate growth rate
            $growthRate = $previousRevenue > 0
                ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
                : 0;

            $performanceData[] = [
                'id' => $unit->id,
                'name' => $unit->unit_name,
                'location' => $unit->city ?? 'Unknown',
                'revenue' => number_format($currentRevenue, 2) . ' SAR',
                'revenue_value' => $currentRevenue,
                'growth' => ($growthRate >= 0 ? '+' : '') . number_format($growthRate, 1) . '%',
            ];
        }

        // Sort by revenue and take top performers
        $topPerformers = collect($performanceData)
            ->sortByDesc('revenue_value')
            ->take($limit)
            ->map(function ($item) {
                unset($item['revenue_value']); // Remove helper field

                return $item;
            })
            ->values();

        return $this->successResponse($topPerformers, 'Top performers retrieved successfully');
    }

    /**
     * Get customer satisfaction statistics - calculated in real-time from reviews
     */
    public function customerSatisfaction(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return $this->notFoundResponse('No franchise found for current user');
        }

        $periodType = $request->get('period_type', 'monthly');

        // Get current and previous period dates
        $currentDate = now();
        $currentStart = match ($periodType) {
            'daily' => $currentDate->copy()->startOfDay(),
            'monthly' => $currentDate->copy()->startOfMonth(),
            'yearly' => $currentDate->copy()->startOfYear(),
            default => $currentDate->copy()->startOfMonth(),
        };

        $previousStart = match ($periodType) {
            'daily' => $currentDate->copy()->subDay()->startOfDay(),
            'monthly' => $currentDate->copy()->subMonth()->startOfMonth(),
            'yearly' => $currentDate->copy()->subYear()->startOfYear(),
            default => $currentDate->copy()->subMonth()->startOfMonth(),
        };

        $previousEnd = $currentStart->copy()->subSecond();

        // Get all units for this franchise
        $unitIds = Unit::where('franchise_id', $franchise->id)->pluck('id');

        // Calculate current period ratings
        $currentRatings = DB::table('reviews')
            ->whereIn('unit_id', $unitIds)
            ->where('created_at', '>=', $currentStart)
            ->where('created_at', '<=', $currentDate)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        // Calculate previous period ratings for trend
        $previousRatings = DB::table('reviews')
            ->whereIn('unit_id', $unitIds)
            ->where('created_at', '>=', $previousStart)
            ->where('created_at', '<=', $previousEnd)
            ->selectRaw('AVG(rating) as avg_rating')
            ->first();

        $currentScore = $currentRatings->avg_rating ? (float) $currentRatings->avg_rating : 0;
        $previousScore = $previousRatings->avg_rating ? (float) $previousRatings->avg_rating : $currentScore;
        $trend = $currentScore - $previousScore;

        $satisfactionData = [
            'score' => round($currentScore, 2),
            'max_score' => 5.0,
            'total_reviews' => (int) $currentRatings->review_count,
            'trend' => ($trend >= 0 ? '+' : '') . number_format($trend, 1),
        ];

        return $this->successResponse($satisfactionData, 'Customer satisfaction data retrieved successfully');
    }

    /**
     * Get top and lowest rated units - calculated in real-time from reviews
     */
    public function ratings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get franchisor's franchise
        $franchise = Franchise::where('franchisor_id', $user->id)->first();

        if (! $franchise) {
            return $this->notFoundResponse('No franchise found for current user');
        }

        $periodType = $request->get('period_type', 'monthly');

        // Get period date range
        $startDate = $this->getStartDate($periodType);
        $endDate = now();

        // Get all units with their ratings
        $units = Unit::where('franchise_id', $franchise->id)->with('franchisee')->get();

        $unitRatings = [];

        foreach ($units as $unit) {
            $ratingData = $this->calculateRating($unit->id, $startDate, $endDate);

            if ($ratingData['count'] > 0) {
                $unitRatings[] = [
                    'id' => $unit->id,
                    'name' => $unit->unit_name,
                    'location' => $unit->city ?? 'Unknown',
                    'rating' => $ratingData['rating'],
                    'reviews' => $ratingData['count'],
                    'manager' => $unit->franchisee?->name ?? 'Unknown',
                ];
            }
        }

        // Sort by rating
        $sortedRatings = collect($unitRatings)->sortByDesc('rating')->values();

        $ratingsData = [
            'top_rated' => $sortedRatings->first(),
            'lowest_rated' => $sortedRatings->count() > 1 ? $sortedRatings->last() : null,
        ];

        return $this->successResponse($ratingsData, 'Ratings data retrieved successfully');
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
            return $this->notFoundResponse('No franchise found for current user');
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

        return $this->successResponse($exportData, 'Export data retrieved successfully');
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
            return $this->notFoundResponse('No franchise found for current user');
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

        return $this->successResponse(collect($allUnits)->concat($units), 'Units retrieved successfully');
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

    /**
     * Extract year and month from period date based on period type
     */
    private function extractYearMonth(Carbon $periodDate, string $periodType): array
    {
        return [$periodDate->year, $periodDate->month];
    }

    /**
     * Calculate revenue for a unit in a given period from revenues table
     */
    private function calculateRevenue(int $unitId, int $year, int $month, string $periodType, Carbon $periodDate): float
    {
        $query = DB::table('revenues')
            ->where('unit_id', $unitId);

        if ($periodType === 'monthly') {
            $query->where('period_year', $year)
                ->where('period_month', $month);
        } elseif ($periodType === 'yearly') {
            $query->where('period_year', $year);
        } elseif ($periodType === 'daily') {
            $query->whereDate('created_at', $periodDate->toDateString());
        }

        return (float) $query->sum('amount');
    }

    /**
     * Calculate expenses for a unit in a given period from transactions table
     */
    private function calculateExpenses(int $unitId, int $year, int $month, string $periodType, Carbon $periodDate): float
    {
        $query = DB::table('transactions')
            ->where('unit_id', $unitId)
            ->where('type', 'expense');

        if ($periodType === 'monthly') {
            $query->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month);
        } elseif ($periodType === 'yearly') {
            $query->whereYear('transaction_date', $year);
        } elseif ($periodType === 'daily') {
            $query->whereDate('transaction_date', $periodDate->toDateString());
        }

        return (float) $query->sum('amount');
    }

    /**
     * Calculate royalties for a unit in a given period from royalties table
     */
    private function calculateRoyalties(int $unitId, int $year, int $month, string $periodType, Carbon $periodDate): float
    {
        $query = DB::table('royalties')
            ->where('unit_id', $unitId);

        if ($periodType === 'monthly') {
            $query->where('period_year', $year)
                ->where('period_month', $month);
        } elseif ($periodType === 'yearly') {
            $query->where('period_year', $year);
        } elseif ($periodType === 'daily') {
            $query->whereDate('created_at', $periodDate->toDateString());
        }

        return (float) $query->sum('total_amount');
    }

    /**
     * Calculate average rating for a unit from reviews table
     */
    private function calculateRating(int $unitId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = DB::table('reviews')
            ->where('unit_id', $unitId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $avgRating = (float) $query->avg('rating');
        $reviewCount = (int) $query->count();

        return [
            'rating' => round($avgRating, 2),
            'count' => $reviewCount,
        ];
    }
}
