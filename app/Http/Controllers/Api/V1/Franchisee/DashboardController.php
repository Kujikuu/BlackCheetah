<?php

namespace App\Http\Controllers\Api\V1\Franchisee;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\Product;
use App\Models\Revenue;
use App\Models\Staff;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Franchisee Dashboard Controller
 * 
 * Handles dashboard analytics and statistics for franchisees
 */
class DashboardController extends Controller
{
    /**
     * Get unit for the current franchisee user
     */
    private function getUserUnit(Request $request): ?Unit
    {
        $user = $request->user();
        return Unit::where('franchisee_id', $user->id)->first();
    }

    /**
     * Get sales statistics for franchisee dashboard widgets
     */
    public function salesStatistics(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        // Get current month and previous month dates
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        // Current month sales and profit
        $currentMonthRevenue = Revenue::where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('period_year', now()->year)
            ->where('period_month', now()->month)
            ->sum('amount');

        $currentMonthProfit = Revenue::where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('period_year', now()->year)
            ->where('period_month', now()->month)
            ->sum('net_amount');

        // Previous month sales and profit for comparison
        $previousMonthRevenue = Revenue::where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('period_year', now()->subMonth()->year)
            ->where('period_month', now()->subMonth()->month)
            ->sum('amount');

        $previousMonthProfit = Revenue::where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('period_year', now()->subMonth()->year)
            ->where('period_month', now()->subMonth()->month)
            ->sum('net_amount');

        // Calculate percentage changes
        $salesChange = $previousMonthRevenue > 0
            ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 2)
            : 0;

        $profitChange = $previousMonthProfit > 0
            ? round((($currentMonthProfit - $previousMonthProfit) / $previousMonthProfit) * 100, 2)
            : 0;

        return $this->successResponse([
            'totalSales' => $currentMonthRevenue,
            'totalProfit' => $currentMonthProfit,
            'salesChange' => $salesChange,
            'profitChange' => $profitChange,
        ], 'Sales statistics retrieved successfully');
    }

    /**
     * Get product sales data (most selling and low selling items)
     */
    public function productSales(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Get all revenues with line items for current month
        $revenues = Revenue::where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('period_year', $currentYear)
            ->where('period_month', $currentMonth)
            ->where('status', 'verified')
            ->whereNotNull('line_items')
            ->get();

        // Aggregate product sales from line items
        $productSales = [];
        foreach ($revenues as $revenue) {
            if (is_array($revenue->line_items)) {
                foreach ($revenue->line_items as $item) {
                    $itemName = $item['product_name'] ?? $item['item_name'] ?? $item['name'] ?? 'Unknown Item';
                    $quantity = $item['quantity'] ?? 0;
                    $unitPrice = $item['unit_price'] ?? $item['price'] ?? 0;

                    if (! isset($productSales[$itemName])) {
                        $productSales[$itemName] = [
                            'name' => $itemName,
                            'total_quantity' => 0,
                            'total_revenue' => 0,
                            'avg_price' => 0,
                            'count' => 0,
                        ];
                    }

                    $productSales[$itemName]['total_quantity'] += $quantity;
                    $productSales[$itemName]['total_revenue'] += ($quantity * $unitPrice);
                    $productSales[$itemName]['count']++;
                }
            }
        }

        // Calculate average prices and sort by quantity
        foreach ($productSales as &$item) {
            $item['avg_price'] = $item['total_quantity'] > 0 ? $item['total_revenue'] / $item['total_quantity'] : 0;
        }
        unset($item);

        // Sort by total quantity descending
        usort($productSales, function ($a, $b) {
            return $b['total_quantity'] <=> $a['total_quantity'];
        });

        // Get top 5 most selling items
        $mostSelling = collect(array_slice($productSales, 0, 5))->map(function ($item) {
            return [
                'name' => $item['name'],
                'quantity' => (int) $item['total_quantity'],
                'avgPrice' => (float) round($item['avg_price'], 2),
            ];
        });

        // Get bottom 5 low selling items
        $lowSelling = collect(array_slice($productSales, -5, 5, true))->reverse()->map(function ($item) {
            return [
                'name' => $item['name'],
                'quantity' => (int) $item['total_quantity'],
                'avgPrice' => (float) round($item['avg_price'], 2),
            ];
        });

        return $this->successResponse([
            'mostSelling' => $mostSelling,
            'lowSelling' => $lowSelling,
        ]);
    }

    /**
     * Get monthly performance data for charts
     */
    public function monthlyPerformance(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        // Get monthly performance data for the current year
        $monthlyData = DB::table('revenues')
            ->where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('period_year', now()->year)
            ->select(
                'period_month',
                DB::raw('SUM(amount) as total_revenue'),
                DB::raw('SUM(net_amount) as total_profit'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('period_month')
            ->orderBy('period_month')
            ->get();

        // Initialize arrays for 12 months
        $topPerforming = array_fill(0, 12, 0);
        $lowPerforming = array_fill(0, 12, 0);
        $averagePerformance = array_fill(0, 12, 0);

        // Fill in actual data
        foreach ($monthlyData as $data) {
            $monthIndex = $data->period_month - 1;
            $topPerforming[$monthIndex] = (int) ($data->total_revenue * 0.8); // Simulate top 80%
            $lowPerforming[$monthIndex] = (int) ($data->total_revenue * 0.2); // Simulate bottom 20%
            $averagePerformance[$monthIndex] = (int) ($data->total_revenue * 0.5); // Simulate average 50%
        }

        return $this->successResponse([
            'topPerforming' => $topPerforming,
            'lowPerforming' => $lowPerforming,
            'averagePerformance' => $averagePerformance,
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ], 'Monthly performance data retrieved successfully');
    }

    /**
     * Get finance statistics for finance dashboard widgets
     */
    public function financeStatistics(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        // Get current month and previous month dates
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $previousMonth = now()->subMonth()->month;
        $previousYear = now()->subMonth()->year;

        // Current month financial data
        $currentSales = Revenue::where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('period_year', $currentYear)
            ->where('period_month', $currentMonth)
            ->sum('amount');

        $currentExpenses = Revenue::where('unit_id', $unit->id)
            ->where('type', 'expense')
            ->where('period_year', $currentYear)
            ->where('period_month', $currentMonth)
            ->sum('amount');

        $currentProfit = $currentSales - $currentExpenses;

        // Previous month financial data for comparison
        $previousSales = Revenue::where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('period_year', $previousYear)
            ->where('period_month', $previousMonth)
            ->sum('amount');

        $previousExpenses = Revenue::where('unit_id', $unit->id)
            ->where('type', 'expense')
            ->where('period_year', $previousYear)
            ->where('period_month', $previousMonth)
            ->sum('amount');

        $previousProfit = $previousSales - $previousExpenses;

        // Calculate percentage changes
        $salesChange = $previousSales > 0
            ? round((($currentSales - $previousSales) / $previousSales) * 100, 2)
            : 0;

        $expensesChange = $previousExpenses > 0
            ? round((($currentExpenses - $previousExpenses) / $previousExpenses) * 100, 2)
            : 0;

        $profitChange = $previousProfit > 0
            ? round((($currentProfit - $previousProfit) / $previousProfit) * 100, 2)
            : 0;

        return $this->successResponse([
            'totalSales' => $currentSales,
            'totalExpenses' => $currentExpenses,
            'totalProfit' => $currentProfit,
            'salesChange' => $salesChange,
            'expensesChange' => $expensesChange,
            'profitChange' => $profitChange,
        ], 'Finance statistics retrieved successfully');
    }

    /**
     * Get financial summary data for charts
     */
    public function financialSummary(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        // Get monthly financial data for the current year
        $monthlyFinancialData = DB::table('revenues')
            ->where('unit_id', $unit->id)
            ->where('period_year', now()->year)
            ->select(
                'period_month',
                'type',
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('period_month', 'type')
            ->orderBy('period_month')
            ->get();

        // Initialize arrays for 12 months
        $sales = array_fill(0, 12, 0);
        $expenses = array_fill(0, 12, 0);
        $profit = array_fill(0, 12, 0);

        // Fill in actual data
        foreach ($monthlyFinancialData as $data) {
            $monthIndex = $data->period_month - 1;
            if ($data->type === 'sales') {
                $sales[$monthIndex] = (float) $data->total_amount;
            } elseif ($data->type === 'expense') {
                $expenses[$monthIndex] = (float) $data->total_amount;
            }
        }

        // Calculate profit for each month
        for ($i = 0; $i < 12; $i++) {
            $profit[$i] = $sales[$i] - $expenses[$i];
        }

        return $this->successResponse([
            'sales' => $sales,
            'expenses' => $expenses,
            'profit' => $profit,
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ], 'Financial summary data retrieved successfully');
    }

    /**
     * Get store data statistics
     */
    public function storeData(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        // Get products for this unit through inventory
        $inventoryData = DB::table('unit_product_inventories')
            ->join('products', 'unit_product_inventories.product_id', '=', 'products.id')
            ->where('unit_product_inventories.unit_id', $unit->id)
            ->select(
                'products.id',
                'products.name',
                'products.stock',
                'products.minimum_stock',
                'unit_product_inventories.quantity as unit_quantity',
                'unit_product_inventories.reorder_level'
            )
            ->get();

        $totalItems = $inventoryData->count();
        $totalStocks = $inventoryData->sum('unit_quantity');

        // Low stock items (where quantity <= reorder_level)
        $lowStockItems = $inventoryData->where('unit_quantity', '<=', 'reorder_level')->count();

        // Out of stock items (where quantity = 0)
        $outOfStockItems = $inventoryData->where('unit_quantity', 0)->count();

        return $this->successResponse([
            'totalItems' => $totalItems,
            'totalStocks' => $totalStocks,
            'lowStockItems' => $lowStockItems,
            'outOfStockItems' => $outOfStockItems,
        ], 'Store data retrieved successfully');
    }

    /**
     * Get staff data statistics
     */
    public function staffData(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        // Get active staff members for this unit
        $staffMembers = $unit->activeStaff()->get();
        $totalStaffs = $staffMembers->count();

        // New hires this month (staff assigned to this unit this month)
        $newHires = $unit->staff()
            ->wherePivot('assigned_date', '>=', now()->startOfMonth())
            ->wherePivotNull('end_date')
            ->count();

        // Mock absenteeism rate (in real app, you'd track attendance)
        $monthlyAbsenteeismRate = rand(5, 15) + (rand(0, 9) / 10);

        // Get top performers (mock based on staff data)
        $topPerformers = $staffMembers->take(5)->map(function ($staff, $index) {
            return [
                'id' => $staff->id,
                'name' => $staff->name,
                'performance' => rand(80, 98), // Mock performance score
                'department' => $staff->department ?? $this->getRandomDepartment(),
            ];
        })->sortByDesc('performance')->values();

        return $this->successResponse([
            'totalStaffs' => $totalStaffs,
            'newHires' => $newHires,
            'monthlyAbsenteeismRate' => $monthlyAbsenteeismRate,
            'topPerformers' => $topPerformers,
        ], 'Staff data retrieved successfully');
    }

    /**
     * Get low stock chart data
     */
    public function lowStockChart(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        // Mock monthly inventory data (in a real app, you'd track this over time)
        $intakeData = [120, 132, 101, 134, 90, 230, 210, 150, 180, 200, 220, 240];
        $availableData = [80, 95, 70, 110, 60, 180, 160, 120, 140, 160, 180, 200];

        return $this->successResponse([
            [
                'name' => 'Intake',
                'data' => $intakeData,
            ],
            [
                'name' => 'Available',
                'data' => $availableData,
            ],
        ], 'Low stock chart data retrieved successfully');
    }

    /**
     * Get shift coverage chart data
     */
    public function shiftCoverageChart(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        // Mock shift coverage data for the week
        return $this->successResponse([
            [
                'name' => 'Total Coverage',
                'data' => [24, 32, 28, 36, 40, 32, 28],
            ],
            [
                'name' => 'Required Coverage',
                'data' => [20, 25, 20, 30, 35, 25, 20],
            ],
        ], 'Shift coverage chart data retrieved successfully');
    }

    /**
     * Get all operations data in one call
     */
    public function operationsData(Request $request): JsonResponse
    {
        $unit = $this->getUserUnit($request);

        if (! $unit) {
            return $this->errorResponse('No unit found for current user', null, 404);
        }

        // Get store data
        $storeDataResponse = $this->storeData($request);
        $storeData = $storeDataResponse->getData(true)['data'];

        // Get staff data
        $staffDataResponse = $this->staffData($request);
        $staffData = $staffDataResponse->getData(true)['data'];

        // Get chart data
        $lowStockChartResponse = $this->lowStockChart($request);
        $lowStockChart = $lowStockChartResponse->getData(true)['data'];

        $shiftCoverageChartResponse = $this->shiftCoverageChart($request);
        $shiftCoverageChart = $shiftCoverageChartResponse->getData(true)['data'];

        return $this->successResponse([
            'storeData' => $storeData,
            'staffData' => $staffData,
            'lowStockChart' => $lowStockChart,
            'shiftCoverageChart' => $shiftCoverageChart,
        ], 'Operations data retrieved successfully');
    }

    /**
     * Get random department for mock data
     */
    private function getRandomDepartment(): string
    {
        $departments = ['Sales', 'Customer Service', 'Inventory', 'Operations', 'Marketing', 'Administration'];

        return $departments[array_rand($departments)];
    }
}
