<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Revenue;
use App\Models\Staff;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FranchiseeDashboardController extends Controller
{
    /**
     * Get sales statistics for franchisee dashboard widgets
     */
    public function salesStatistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
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

        return response()->json([
            'success' => true,
            'data' => [
                'totalSales' => $currentMonthRevenue,
                'totalProfit' => $currentMonthProfit,
                'salesChange' => $salesChange,
                'profitChange' => $profitChange,
            ],
            'message' => 'Sales statistics retrieved successfully',
        ]);
    }

    /**
     * Get product sales data (most selling and low selling items)
     */
    public function productSales(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
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
                    $itemName = $item['item_name'] ?? $item['name'] ?? 'Unknown Item';
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

        return response()->json([
            'success' => true,
            'data' => [
                'mostSelling' => $mostSelling,
                'lowSelling' => $lowSelling,
            ],
        ]);
    }

    /**
     * Get monthly performance data for charts
     */
    public function monthlyPerformance(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
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

        return response()->json([
            'success' => true,
            'data' => [
                'topPerforming' => $topPerforming,
                'lowPerforming' => $lowPerforming,
                'averagePerformance' => $averagePerformance,
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            ],
            'message' => 'Monthly performance data retrieved successfully',
        ]);
    }

    /**
     * Get finance statistics for finance dashboard widgets
     */
    public function financeStatistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
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

        return response()->json([
            'success' => true,
            'data' => [
                'totalSales' => $currentSales,
                'totalExpenses' => $currentExpenses,
                'totalProfit' => $currentProfit,
                'salesChange' => $salesChange,
                'expensesChange' => $expensesChange,
                'profitChange' => $profitChange,
            ],
            'message' => 'Finance statistics retrieved successfully',
        ]);
    }

    /**
     * Get financial summary data for charts
     */
    public function financialSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
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

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $sales,
                'expenses' => $expenses,
                'profit' => $profit,
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            ],
            'message' => 'Financial summary data retrieved successfully',
        ]);
    }

    /**
     * Get store data statistics
     */
    public function storeData(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
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

        return response()->json([
            'success' => true,
            'data' => [
                'totalItems' => $totalItems,
                'totalStocks' => $totalStocks,
                'lowStockItems' => $lowStockItems,
                'outOfStockItems' => $outOfStockItems,
            ],
            'message' => 'Store data retrieved successfully',
        ]);
    }

    /**
     * Get staff data statistics
     */
    public function staffData(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
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

        return response()->json([
            'success' => true,
            'data' => [
                'totalStaffs' => $totalStaffs,
                'newHires' => $newHires,
                'monthlyAbsenteeismRate' => $monthlyAbsenteeismRate,
                'topPerformers' => $topPerformers,
            ],
            'message' => 'Staff data retrieved successfully',
        ]);
    }

    /**
     * Get low stock chart data
     */
    public function lowStockChart(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
        }

        // Mock monthly inventory data (in a real app, you'd track this over time)
        $intakeData = [120, 132, 101, 134, 90, 230, 210, 150, 180, 200, 220, 240];
        $availableData = [80, 95, 70, 110, 60, 180, 160, 120, 140, 160, 180, 200];

        return response()->json([
            'success' => true,
            'data' => [
                [
                    'name' => 'Intake',
                    'data' => $intakeData,
                ],
                [
                    'name' => 'Available',
                    'data' => $availableData,
                ],
            ],
            'message' => 'Low stock chart data retrieved successfully',
        ]);
    }

    /**
     * Get shift coverage chart data
     */
    public function shiftCoverageChart(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
        }

        // Mock shift coverage data for the week
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'name' => 'Morning Shift',
                    'data' => [8, 7, 8, 6, 7, 8, 7],
                ],
                [
                    'name' => 'Afternoon Shift',
                    'data' => [6, 8, 7, 8, 6, 7, 8],
                ],
                [
                    'name' => 'Evening Shift',
                    'data' => [4, 5, 6, 5, 4, 5, 6],
                ],
                [
                    'name' => 'Night Shift',
                    'data' => [2, 3, 2, 3, 2, 2, 3],
                ],
            ],
            'message' => 'Shift coverage chart data retrieved successfully',
        ]);
    }

    /**
     * Get all operations data in one call
     */
    public function operationsData(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No unit found for current user',
            ], 404);
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

        return response()->json([
            'success' => true,
            'data' => [
                'storeData' => $storeData,
                'staffData' => $staffData,
                'lowStockChart' => $lowStockChart,
                'shiftCoverageChart' => $shiftCoverageChart,
            ],
            'message' => 'Operations data retrieved successfully',
        ]);
    }

    /**
     * Get random department for mock data
     */
    private function getRandomDepartment(): string
    {
        $departments = ['Sales', 'Customer Service', 'Inventory', 'Operations', 'Marketing', 'Administration'];

        return $departments[array_rand($departments)];
    }

    /**
     * Get unit details and overview information
     */
    public function unitDetails(Request $request, $unitId = null): JsonResponse
    {
        $user = $request->user();

        // If unitId is provided, get specific unit, otherwise get user's unit
        if ($unitId) {
            $unit = Unit::where('id', $unitId)
                ->where('franchisee_id', $user->id)
                ->with(['franchise', 'franchisee'])
                ->first();
        } else {
            $unit = Unit::where('franchisee_id', $user->id)
                ->with(['franchise', 'franchisee'])
                ->first();
        }

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $unit->id,
                'branchName' => $unit->unit_name,
                'franchiseeName' => $unit->franchisee->name,
                'email' => $unit->franchisee->email,
                'contactNumber' => $unit->phone ?? $unit->franchisee->phone,
                'address' => $unit->address,
                'city' => $unit->city,
                'state' => $unit->state_province,
                'country' => $unit->country,
                'royaltyPercentage' => $unit->franchise->royalty_percentage ?? 8.5,
                'contractStartDate' => $unit->lease_start_date?->format('Y-m-d'),
                'renewalDate' => $unit->lease_end_date?->format('Y-m-d'),
                'status' => $unit->status,
            ],
            'message' => 'Unit details retrieved successfully',
        ]);
    }

    /**
     * Get unit tasks
     */
    public function unitTasks(Request $request, $unitId = null): JsonResponse
    {
        $user = $request->user();

        if ($unitId) {
            $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();
        } else {
            $unit = Unit::where('franchisee_id', $user->id)->first();
        }

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $tasks = $unit->tasks()->latest()->get()->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description ?? '',
                'category' => $task->category ?? 'Operations',
                'assignedTo' => $task->assigned_to ?? 'Unassigned',
                'startDate' => $task->start_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'dueDate' => $task->due_date?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d'),
                'priority' => strtolower($task->priority ?? 'medium'),
                'status' => strtolower(str_replace(' ', '_', $task->status ?? 'pending')),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tasks,
            'message' => 'Unit tasks retrieved successfully',
        ]);
    }

    /**
     * Get unit staff members
     */
    public function unitStaff(Request $request, $unitId = null): JsonResponse
    {
        $user = $request->user();

        if ($unitId) {
            $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();
        } else {
            $unit = Unit::where('franchisee_id', $user->id)->first();
        }

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        // Get active staff members for this unit
        $staffMembers = $unit->activeStaff()
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'phone' => $staff->phone,
                    'jobTitle' => $staff->job_title,
                    'department' => $staff->department,
                    'salary' => $staff->salary,
                    'hireDate' => $staff->hire_date?->format('Y-m-d'),
                    'shiftStart' => $staff->shift_start?->format('H:i'),
                    'shiftEnd' => $staff->shift_end?->format('H:i'),
                    'shiftTime' => $staff->full_shift_time,
                    'status' => $staff->status === 'active' ? 'working' : ($staff->status === 'on_leave' ? 'leave' : $staff->status),
                    'employmentType' => $staff->employment_type,
                    'notes' => $staff->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $staffMembers,
            'message' => 'Unit staff retrieved successfully',
        ]);
    }

    /**
     * Get unit products/inventory
     */
    public function unitProducts(Request $request, $unitId = null): JsonResponse
    {
        $user = $request->user();

        if ($unitId) {
            $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();
        } else {
            $unit = Unit::where('franchisee_id', $user->id)->first();
        }

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        // Get products with inventory data
        $products = DB::table('unit_product_inventories')
            ->join('products', 'unit_product_inventories.product_id', '=', 'products.id')
            ->where('unit_product_inventories.unit_id', $unit->id)
            ->select(
                'products.id',
                'products.name',
                'products.description',
                'products.unit_price as unitPrice',
                'products.category',
                'products.status',
                'unit_product_inventories.quantity as stock'
            )
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description ?? 'Product description',
                    'unitPrice' => (float) $product->unitPrice,
                    'category' => $product->category ?? 'General',
                    'status' => $product->status ?? 'active',
                    'stock' => (int) $product->stock,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Unit products retrieved successfully',
        ]);
    }

    /**
     * Get unit reviews
     */
    public function unitReviews(Request $request, $unitId = null): JsonResponse
    {
        $user = $request->user();

        if ($unitId) {
            $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();
        } else {
            $unit = Unit::where('franchisee_id', $user->id)->first();
        }

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $reviews = $unit->reviews()->latest()->get()->map(function ($review) {
            return [
                'id' => $review->id,
                'customerName' => $review->customer_name ?? 'Anonymous Customer',
                'customerEmail' => $review->customer_email ?? '',
                'rating' => (int) $review->rating,
                'comment' => $review->comment ?? '',
                'date' => $review->review_date ? $review->review_date->format('Y-m-d') : $review->created_at->format('Y-m-d'),
                'sentiment' => $review->rating >= 4 ? 'positive' : ($review->rating >= 3 ? 'neutral' : 'negative'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'message' => 'Unit reviews retrieved successfully',
        ]);
    }

    /**
     * Get unit documents
     */
    public function unitDocuments(Request $request, $unitId = null): JsonResponse
    {
        $user = $request->user();

        if ($unitId) {
            $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();
        } else {
            $unit = Unit::where('franchisee_id', $user->id)->first();
        }

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        // Get documents from the unit's documents JSON field
        $documents = collect($unit->documents ?? [])->map(function ($doc, $index) {
            return [
                'id' => $index + 1,
                'title' => $doc['title'] ?? 'Document',
                'description' => $doc['description'] ?? 'Document description',
                'fileName' => $doc['fileName'] ?? $doc['filename'] ?? 'document.pdf',
                'fileSize' => $doc['fileSize'] ?? $doc['size'] ?? '1.2 MB',
                'uploadDate' => $doc['uploadDate'] ?? $doc['date'] ?? now()->format('Y-m-d'),
                'type' => $doc['type'] ?? 'General',
                'status' => $doc['status'] ?? 'approved',
                'comment' => $doc['comment'] ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $documents,
            'message' => 'Unit documents retrieved successfully',
        ]);
    }

    // CRUD Operations for Unit Management

    /**
     * Update unit details
     */
    public function updateUnitDetails(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $validated = $request->validate([
            'branchName' => 'sometimes|string|max:255',
            'contactNumber' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
        ]);

        $unit->update([
            'unit_name' => $validated['branchName'] ?? $unit->unit_name,
            'phone' => $validated['contactNumber'] ?? $unit->phone,
            'address' => $validated['address'] ?? $unit->address,
            'city' => $validated['city'] ?? $unit->city,
            'state_province' => $validated['state'] ?? $unit->state_province,
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->formatUnitDetails($unit),
            'message' => 'Unit details updated successfully',
        ]);
    }

    /**
     * Create a new task
     */
    public function createTask(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category' => 'required|in:onboarding,training,compliance,maintenance,marketing,operations,finance,support,other',
            'assignedTo' => 'required|string|max:255',
            'startDate' => 'nullable|date',
            'dueDate' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled,on_hold',
        ]);

        $task = $unit->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['category'],
            'created_by' => $user->id,
            'franchise_id' => $unit->franchise_id,
            'due_date' => $validated['dueDate'],
            'priority' => $validated['priority'],
            'status' => $validated['status'] ?? 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'category' => $task->type,
                'assignedTo' => $validated['assignedTo'],
                'startDate' => $validated['startDate'] ?? null,
                'dueDate' => $task->due_date,
                'priority' => $task->priority,
                'status' => $task->status,
            ],
            'message' => 'Task created successfully',
        ]);
    }

    /**
     * Update a task
     */
    public function updateTask(Request $request, $unitId, $taskId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $task = $unit->tasks()->where('id', $taskId)->first();
        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'category' => 'sometimes|string|max:100',
            'assignedTo' => 'sometimes|string|max:255',
            'startDate' => 'sometimes|date',
            'dueDate' => 'sometimes|date',
            'priority' => 'sometimes|in:low,medium,high',
            'status' => 'sometimes|in:pending,in_progress,completed',
        ]);

        $task->update([
            'title' => $validated['title'] ?? $task->title,
            'description' => $validated['description'] ?? $task->description,
            'category' => $validated['category'] ?? $task->category,
            'assigned_to' => $validated['assignedTo'] ?? $task->assigned_to,
            'start_date' => $validated['startDate'] ?? $task->start_date,
            'due_date' => $validated['dueDate'] ?? $task->due_date,
            'priority' => $validated['priority'] ?? $task->priority,
            'status' => $validated['status'] ?? $task->status,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'category' => $task->category,
                'assignedTo' => $task->assigned_to,
                'startDate' => $task->start_date,
                'dueDate' => $task->due_date,
                'priority' => $task->priority,
                'status' => $task->status,
            ],
            'message' => 'Task updated successfully',
        ]);
    }

    /**
     * Delete a task
     */
    public function deleteTask(Request $request, $unitId, $taskId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $task = $unit->tasks()->where('id', $taskId)->first();
        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Create a new staff member
     */
    public function createStaff(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:staff,email',
            'phone' => 'nullable|string|max:20',
            'jobTitle' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'hireDate' => 'required|date',
            'shiftStart' => 'nullable|date_format:H:i',
            'shiftEnd' => 'nullable|date_format:H:i',
            'status' => 'sometimes|in:Active,On Leave,Terminated,Inactive',
            'employmentType' => 'sometimes|in:full_time,part_time,contract,temporary',
            'notes' => 'nullable|string',
        ]);

        $staff = Staff::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'job_title' => $validated['jobTitle'],
            'department' => $validated['department'] ?? null,
            'salary' => $validated['salary'] ?? null,
            'hire_date' => $validated['hireDate'],
            'shift_start' => $validated['shiftStart'] ?? null,
            'shift_end' => $validated['shiftEnd'] ?? null,
            'status' => strtolower(str_replace(' ', '_', $validated['status'] ?? 'active')),
            'employment_type' => $validated['employmentType'] ?? 'full_time',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Attach staff to unit
        $staff->units()->attach($unit->id);

        // Refresh to get accessor values
        $staff->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'jobTitle' => $staff->job_title,
                'department' => $staff->department,
                'salary' => $staff->salary,
                'hireDate' => $staff->hire_date?->format('Y-m-d'),
                'shiftStart' => $staff->shift_start?->format('H:i'),
                'shiftEnd' => $staff->shift_end?->format('H:i'),
                'shiftTime' => $staff->full_shift_time,
                'status' => $staff->status === 'active' ? 'working' : ($staff->status === 'on_leave' ? 'leave' : $staff->status),
                'employmentType' => $staff->employment_type,
                'notes' => $staff->notes,
            ],
            'message' => 'Staff member created successfully',
        ]);
    }

    /**
     * Update a staff member
     */
    public function updateStaff(Request $request, $unitId, $staffId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $staff = $unit->staff()->where('staff.id', $staffId)->first();
        if (! $staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:staff,email,' . $staffId,
            'phone' => 'sometimes|nullable|string|max:20',
            'jobTitle' => 'sometimes|string|max:100',
            'department' => 'sometimes|nullable|string|max:100',
            'salary' => 'sometimes|nullable|numeric|min:0',
            'hireDate' => 'sometimes|date',
            'shiftStart' => 'sometimes|nullable|date_format:H:i',
            'shiftEnd' => 'sometimes|nullable|date_format:H:i',
            'status' => 'sometimes|in:working,leave,terminated,inactive',
            'employmentType' => 'sometimes|in:full_time,part_time,contract,temporary',
            'notes' => 'sometimes|nullable|string',
        ]);

        // Map frontend status values to database values
        $statusMapping = [
            'working' => 'active',
            'leave' => 'on_leave',
            'terminated' => 'terminated',
            'inactive' => 'inactive',
        ];

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (isset($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }
        if (isset($validated['phone'])) {
            $updateData['phone'] = $validated['phone'];
        }
        if (isset($validated['jobTitle'])) {
            $updateData['job_title'] = $validated['jobTitle'];
        }
        if (isset($validated['department'])) {
            $updateData['department'] = $validated['department'];
        }
        if (isset($validated['salary'])) {
            $updateData['salary'] = $validated['salary'];
        }
        if (isset($validated['hireDate'])) {
            $updateData['hire_date'] = $validated['hireDate'];
        }
        if (isset($validated['shiftStart'])) {
            $updateData['shift_start'] = $validated['shiftStart'];
        }
        if (isset($validated['shiftEnd'])) {
            $updateData['shift_end'] = $validated['shiftEnd'];
        }
        if (isset($validated['status'])) {
            $updateData['status'] = $statusMapping[$validated['status']] ?? $validated['status'];
        }
        if (isset($validated['employmentType'])) {
            $updateData['employment_type'] = $validated['employmentType'];
        }
        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $staff->update($updateData);

        // Reload the staff to get the full_shift_time accessor
        $staff->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'jobTitle' => $staff->job_title,
                'department' => $staff->department,
                'salary' => $staff->salary,
                'hireDate' => $staff->hire_date?->format('Y-m-d'),
                'shiftStart' => $staff->shift_start?->format('H:i'),
                'shiftEnd' => $staff->shift_end?->format('H:i'),
                'shiftTime' => $staff->full_shift_time,
                'status' => $staff->status === 'active' ? 'working' : ($staff->status === 'on_leave' ? 'leave' : $staff->status),
                'employmentType' => $staff->employment_type,
                'notes' => $staff->notes,
            ],
            'message' => 'Staff member updated successfully',
        ]);
    }

    /**
     * Delete a staff member
     */
    public function deleteStaff(Request $request, $unitId, $staffId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $staff = $unit->staff()->where('staff.id', $staffId)->first();
        if (! $staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member not found',
            ], 404);
        }

        // Remove staff from unit
        $staff->units()->detach($unit->id);

        // If staff is not associated with any other units, delete the staff record
        if ($staff->units()->count() === 0) {
            $staff->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff member removed successfully',
        ]);
    }

    /**
     * Get available franchise products that can be added to unit inventory
     */
    public function getAvailableFranchiseProducts(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        // Get franchise products that are not yet in this unit's inventory
        $availableProducts = Product::where('franchise_id', $unit->franchise_id)
            ->whereDoesntHave('units', function ($query) use ($unit) {
                $query->where('units.id', $unit->id);
            })
            ->where('status', 'active')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'unitPrice' => (float) $product->unit_price,
                    'category' => $product->category,
                    'status' => $product->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $availableProducts,
            'message' => 'Available franchise products retrieved successfully',
        ]);
    }

    /**
     * Add a franchise product to unit inventory
     */
    public function addProductToInventory(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $validated = $request->validate([
            'productId' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'reorderLevel' => 'required|integer|min:0',
        ]);

        // Verify the product belongs to the same franchise
        $product = Product::where('id', $validated['productId'])
            ->where('franchise_id', $unit->franchise_id)
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or not available for this franchise',
            ], 404);
        }

        // Check if product is already in unit inventory
        if ($product->units()->where('units.id', $unit->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already in unit inventory',
            ], 400);
        }

        // Add product to unit inventory
        $product->units()->attach($unit->id, [
            'quantity' => $validated['quantity'],
            'reorder_level' => $validated['reorderLevel'],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'unitPrice' => (float) $product->unit_price,
                'category' => $product->category,
                'status' => $product->status,
                'stock' => $validated['quantity'],
            ],
            'message' => 'Product added to inventory successfully',
        ]);
    }

    /**
     * Update inventory stock levels
     */
    public function updateInventoryStock(Request $request, $unitId, $productId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'reorderLevel' => 'sometimes|integer|min:0',
        ]);

        // Check if product exists in unit inventory
        $inventory = $unit->inventory()->where('product_id', $productId)->first();

        if (! $inventory) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in unit inventory',
            ], 404);
        }

        // Update inventory levels
        $updateData = ['quantity' => $validated['quantity']];
        if (isset($validated['reorderLevel'])) {
            $updateData['reorder_level'] = $validated['reorderLevel'];
        }

        $unit->inventory()->updateExistingPivot($productId, $updateData);

        return response()->json([
            'success' => true,
            'message' => 'Inventory stock updated successfully',
        ]);
    }

    /**
     * Remove product from unit inventory
     */
    public function removeProductFromInventory(Request $request, $unitId, $productId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        // Check if product exists in unit inventory
        if (! $unit->products()->where('product_id', $productId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in unit inventory',
            ], 404);
        }

        // Remove product from inventory
        $unit->products()->detach($productId);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from inventory successfully',
        ]);
    }

    /**
     * Update a product inventory in the unit
     */
    public function updateProduct(Request $request, $unitId, $productId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        // Check if product exists in unit inventory
        if (! $unit->products()->where('product_id', $productId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in unit inventory',
            ], 404);
        }

        $validated = $request->validate([
            'stock' => 'sometimes|integer|min:0',
            'reorderLevel' => 'sometimes|integer|min:0',
        ]);

        // Update pivot table data
        $updateData = [];
        if (isset($validated['stock'])) {
            $updateData['quantity'] = $validated['stock'];
        }
        if (isset($validated['reorderLevel'])) {
            $updateData['reorder_level'] = $validated['reorderLevel'];
        }

        if (! empty($updateData)) {
            $unit->products()->updateExistingPivot($productId, $updateData);
        }

        // Get updated product data
        $productData = DB::table('unit_product_inventories')
            ->join('products', 'unit_product_inventories.product_id', '=', 'products.id')
            ->where('unit_product_inventories.unit_id', $unit->id)
            ->where('unit_product_inventories.product_id', $productId)
            ->select(
                'products.id',
                'products.name',
                'products.description',
                'products.unit_price as unitPrice',
                'products.category',
                'products.status',
                'unit_product_inventories.quantity as stock',
                'unit_product_inventories.reorder_level as reorderLevel'
            )
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $productData->id,
                'name' => $productData->name,
                'description' => $productData->description ?? '',
                'unitPrice' => (float) $productData->unitPrice,
                'category' => $productData->category ?? 'General',
                'status' => $productData->status ?? 'active',
                'stock' => (int) $productData->stock,
            ],
            'message' => 'Product inventory updated successfully',
        ]);
    }

    /**
     * Delete a product
     */
    public function deleteProduct(Request $request, $unitId, $productId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $product = Product::where('id', $productId)
            ->where('franchise_id', $unit->franchise_id)
            ->whereHas('units', function ($query) use ($unit) {
                $query->where('units.id', $unit->id);
            })
            ->first();
        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Create a new review
     */
    public function createReview(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $validated = $request->validate([
            'customerName' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'date' => 'sometimes|date',
        ]);

        $review = $unit->reviews()->create([
            'customer_name' => $validated['customerName'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'review_date' => $validated['date'] ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $review->id,
                'customerName' => $review->customer_name,
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'date' => $review->review_date ?? $review->created_at->format('Y-m-d'),
                'sentiment' => $review->rating >= 4 ? 'positive' : ($review->rating >= 3 ? 'neutral' : 'negative'),
            ],
            'message' => 'Review created successfully',
        ]);
    }

    /**
     * Update a review
     */
    public function updateReview(Request $request, $unitId, $reviewId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $review = $unit->reviews()->where('id', $reviewId)->first();
        if (! $review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        $validated = $request->validate([
            'customerName' => 'sometimes|string|max:255',
            'customerEmail' => 'sometimes|nullable|email|max:255',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|max:1000',
            'date' => 'sometimes|date',
        ]);

        $review->update([
            'customer_name' => $validated['customerName'] ?? $review->customer_name,
            'customer_email' => $validated['customerEmail'] ?? $review->customer_email,
            'rating' => $validated['rating'] ?? $review->rating,
            'comment' => $validated['comment'] ?? $review->comment,
            'review_date' => $validated['date'] ?? $review->review_date,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $review->id,
                'customerName' => $review->customer_name,
                'customerEmail' => $review->customer_email ?? '',
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'date' => $review->review_date ? $review->review_date->format('Y-m-d') : $review->created_at->format('Y-m-d'),
                'sentiment' => $review->rating >= 4 ? 'positive' : ($review->rating >= 3 ? 'neutral' : 'negative'),
            ],
            'message' => 'Review updated successfully',
        ]);
    }

    /**
     * Delete a review
     */
    public function deleteReview(Request $request, $unitId, $reviewId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $review = $unit->reviews()->where('id', $reviewId)->first();
        if (! $review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Create a new document
     */
    public function createDocument(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'file' => 'required|file|max:10240', // Max 10MB
            'type' => 'required|string|max:100',
            'status' => 'sometimes|in:approved,pending,rejected',
            'comment' => 'nullable|string|max:500',
        ]);

        // Store the uploaded file
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        // Generate unique filename to prevent collisions
        $storedFileName = time() . '_' . $fileName;
        $filePath = $file->storeAs('documents/units/' . $unitId, $storedFileName, 'local');

        $documents = $unit->documents ?? [];
        $newDocument = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'fileName' => $fileName,
            'fileSize' => $this->formatFileSize($fileSize),
            'filePath' => $filePath, // Store the actual file path
            'uploadDate' => now()->format('Y-m-d'),
            'type' => $validated['type'],
            'status' => $validated['status'] ?? 'pending',
            'comment' => $validated['comment'] ?? '',
        ];

        $documents[] = $newDocument;
        $unit->update(['documents' => $documents]);

        $documentId = count($documents);
        $newDocument['id'] = $documentId;

        return response()->json([
            'success' => true,
            'data' => $newDocument,
            'message' => 'Document created successfully',
        ]);
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Update a document
     */
    public function updateDocument(Request $request, $unitId, $documentId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $documents = $unit->documents ?? [];
        $docIndex = $documentId - 1;

        if (! isset($documents[$docIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'fileName' => 'sometimes|string|max:255',
            'fileSize' => 'sometimes|string|max:50',
            'type' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:approved,pending,rejected',
            'comment' => 'sometimes|string|max:500',
        ]);

        $documents[$docIndex] = array_merge($documents[$docIndex], $validated);
        $unit->update(['documents' => $documents]);

        $updatedDocument = $documents[$docIndex];
        $updatedDocument['id'] = $documentId;

        return response()->json([
            'success' => true,
            'data' => $updatedDocument,
            'message' => 'Document updated successfully',
        ]);
    }

    /**
     * Delete a document
     */
    public function deleteDocument(Request $request, $unitId, $documentId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $documents = $unit->documents ?? [];
        $docIndex = $documentId - 1;

        if (! isset($documents[$docIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
            ], 404);
        }

        unset($documents[$docIndex]);
        $documents = array_values($documents); // Re-index array
        $unit->update(['documents' => $documents]);

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully',
        ]);
    }

    /**
     * Download a document
     */
    public function downloadDocument(Request $request, $unitId, $documentId)
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ], 404);
        }

        $documents = $unit->documents ?? [];
        $docIndex = $documentId - 1;

        if (! isset($documents[$docIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
            ], 404);
        }

        $document = $documents[$docIndex];

        // Check if filePath exists in the document metadata
        if (isset($document['filePath']) && Storage::exists($document['filePath'])) {
            // File exists - return it for download
            return Storage::download($document['filePath'], $document['fileName']);
        }

        // File doesn't exist - return error
        return response()->json([
            'success' => false,
            'message' => 'File not found in storage. The document may have been uploaded before file storage was implemented.',
        ], 404);
    }

    /**
     * Helper method to format unit details
     */
    private function formatUnitDetails($unit)
    {
        return [
            'id' => $unit->id,
            'branchName' => $unit->unit_name,
            'franchiseeName' => $unit->franchise->business_name ?? 'N/A',
            'email' => $unit->email ?? $unit->franchisee->email ?? 'N/A',
            'contactNumber' => $unit->phone ?? 'N/A',
            'address' => $unit->address ?? 'N/A',
            'city' => $unit->city ?? 'N/A',
            'state' => $unit->state_province ?? 'N/A',
            'country' => $unit->country ?? 'Saudi Arabia',
            'royaltyPercentage' => $unit->franchise->royalty_percentage ?? 0,
            'contractStartDate' => $unit->lease_start_date ?? null,
            'renewalDate' => $unit->lease_end_date ?? null,
            'status' => $unit->status ?? 'active',
        ];
    }

    /**
     * Get role display name
     */
    private function getRoleDisplayName(string $role): string
    {
        return match ($role) {
            'franchisee' => 'Store Manager',
            'manager' => 'Assistant Manager',
            'staff' => 'Sales Associate',
            'admin' => 'Administrator',
            default => ucfirst($role),
        };
    }

    /**
     * Get random shift time for mock data
     */
    private function getRandomShiftTime(): string
    {
        $shifts = [
            '6:00 AM - 2:00 PM',
            '9:00 AM - 6:00 PM',
            '2:00 PM - 10:00 PM',
            '10:00 AM - 7:00 PM',
            '1:00 PM - 9:00 PM',
        ];

        return $shifts[array_rand($shifts)];
    }
}
