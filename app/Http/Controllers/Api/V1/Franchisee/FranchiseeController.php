<?php

namespace App\Http\Controllers\Api\V1\Franchisee;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\StoreFinancialDataRequest;
use App\Http\Requests\UpdateFinancialDataRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Requests\CreateUnitTaskRequest;
use App\Http\Requests\UpdateUnitTaskRequest;
use App\Models\Product;
use App\Models\Revenue;
use App\Models\Staff;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FranchiseeController extends Controller
{
    /**
     * Get sales statistics for franchisee dashboard widgets
     */
    public function salesStatistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        // Mock shift coverage data for the week
        return $this->successResponse([
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
        ], 'Shift coverage chart data retrieved successfully');
    }

    /**
     * Get all operations data in one call
     */
    public function operationsData(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
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
            return $this->notFoundResponse('Unit not found or access denied');
        }

        return $this->successResponse([
            'id' => $unit->id,
            'branchName' => $unit->unit_name,
            'franchiseeName' => $unit->franchisee->name,
            'email' => $unit->franchisee->email,
            'contactNumber' => $unit->phone ?? $unit->franchisee->phone,
            'address' => $unit->address,
            'city' => $unit->city,
            'state' => $unit->state_province,
            'nationality' => $unit->nationality,
            'royaltyPercentage' => $unit->franchise->royalty_percentage ?? 8.5,
            'contractStartDate' => $unit->lease_start_date?->format('Y-m-d'),
            'renewalDate' => $unit->lease_end_date?->format('Y-m-d'),
            'status' => $unit->status,
        ], 'Unit details retrieved successfully');
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
            return $this->notFoundResponse('Unit not found or access denied');
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

        return $this->successResponse($tasks, 'Unit tasks retrieved successfully');
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
            return $this->notFoundResponse('Unit not found or access denied');
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

        return $this->successResponse($staffMembers, 'Unit staff retrieved successfully');
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
            return $this->notFoundResponse('Unit not found or access denied');
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

        return $this->successResponse($products, 'Unit products retrieved successfully');
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
            return $this->notFoundResponse('Unit not found or access denied');
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

        return $this->successResponse($reviews, 'Unit reviews retrieved successfully');
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
            return $this->notFoundResponse('Unit not found or access denied');
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

        return $this->successResponse($documents, 'Unit documents retrieved successfully');
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
            return $this->notFoundResponse('Unit not found or access denied');
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

        return $this->successResponse($this->formatUnitDetails($unit), 'Unit details updated successfully');
    }

    /**
     * Create a new task
     */
    public function createTask(CreateUnitTaskRequest $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $validated = $request->validated();

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

        return $this->successResponse([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'category' => $task->type,
            'assignedTo' => $validated['assignedTo'],
            'startDate' => $validated['startDate'] ?? null,
            'dueDate' => $task->due_date,
            'priority' => $task->priority,
            'status' => $task->status,
        ], 'Task created successfully', 201);
    }

    /**
     * Update a task
     */
    public function updateTask(UpdateUnitTaskRequest $request, $unitId, $taskId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $task = $unit->tasks()->where('id', $taskId)->first();
        if (! $task) {
            return $this->notFoundResponse('Task not found');
        }

        $validated = $request->validated();

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

        return $this->successResponse([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'category' => $task->category,
            'assignedTo' => $task->assigned_to,
            'startDate' => $task->start_date,
            'dueDate' => $task->due_date,
            'priority' => $task->priority,
            'status' => $task->status,
        ], 'Task updated successfully');
    }

    /**
     * Delete a task
     */
    public function deleteTask(Request $request, $unitId, $taskId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $task = $unit->tasks()->where('id', $taskId)->first();
        if (! $task) {
            return $this->notFoundResponse('Task not found');
        }

        $task->delete();

        return $this->successResponse(null, 'Task deleted successfully');
    }

    /**
     * Create a new staff member
     */
    public function createStaff(StoreStaffRequest $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $validated = $request->validated();

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

        return $this->successResponse([
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
        ], 'Staff member created successfully', 201);
    }

    /**
     * Update a staff member
     */
    public function updateStaff(UpdateStaffRequest $request, $unitId, $staffId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $staff = $unit->staff()->where('staff.id', $staffId)->first();
        if (! $staff) {
            return $this->notFoundResponse('Staff member not found');
        }

        $validated = $request->validated();

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

        return $this->successResponse([
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
        ], 'Staff member updated successfully');
    }

    /**
     * Delete a staff member
     */
    public function deleteStaff(Request $request, $unitId, $staffId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $staff = $unit->staff()->where('staff.id', $staffId)->first();
        if (! $staff) {
            return $this->notFoundResponse('Staff member not found');
        }

        // Remove staff from unit
        $staff->units()->detach($unit->id);

        // If staff is not associated with any other units, delete the staff record
        if ($staff->units()->count() === 0) {
            $staff->delete();
        }

        return $this->successResponse(null, 'Staff member removed successfully');
    }

    /**
     * Get available franchise products that can be added to unit inventory
     */
    public function getAvailableFranchiseProducts(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
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

        return $this->successResponse($availableProducts, 'Available franchise products retrieved successfully');
    }

    /**
     * Add a franchise product to unit inventory
     */
    public function addProductToInventory(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
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
            return $this->notFoundResponse('Product not found or not available for this franchise');
        }

        // Check if product is already in unit inventory
        if ($product->units()->where('units.id', $unit->id)->exists()) {
            return $this->errorResponse('Product is already in unit inventory', null, 400);
        }

        // Add product to unit inventory
        $product->units()->attach($unit->id, [
            'quantity' => $validated['quantity'],
            'reorder_level' => $validated['reorderLevel'],
        ]);

        return $this->successResponse([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'unitPrice' => (float) $product->unit_price,
            'category' => $product->category,
            'status' => $product->status,
            'stock' => $validated['quantity'],
        ], 'Product added to inventory successfully', 201);
    }

    /**
     * Update inventory stock levels
     */
    public function updateInventoryStock(Request $request, $unitId, $productId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'reorderLevel' => 'sometimes|integer|min:0',
        ]);

        // Check if product exists in unit inventory
        $inventory = $unit->inventory()->where('product_id', $productId)->first();

        if (! $inventory) {
            return $this->notFoundResponse('Product not found in unit inventory');
        }

        // Update inventory levels
        $updateData = ['quantity' => $validated['quantity']];
        if (isset($validated['reorderLevel'])) {
            $updateData['reorder_level'] = $validated['reorderLevel'];
        }

        $unit->inventory()->updateExistingPivot($productId, $updateData);

        return $this->successResponse(null, 'Inventory stock updated successfully');
    }

    /**
     * Remove product from unit inventory
     */
    public function removeProductFromInventory(Request $request, $unitId, $productId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        // Check if product exists in unit inventory
        if (! $unit->products()->where('product_id', $productId)->exists()) {
            return $this->notFoundResponse('Product not found in unit inventory');
        }

        // Remove product from inventory
        $unit->products()->detach($productId);

        return $this->successResponse(null, 'Product removed from inventory successfully');
    }

    /**
     * Update a product inventory in the unit
     */
    public function updateProduct(Request $request, $unitId, $productId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        // Check if product exists in unit inventory
        if (! $unit->products()->where('product_id', $productId)->exists()) {
            return $this->notFoundResponse('Product not found in unit inventory');
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

        return $this->successResponse([
            'id' => $productData->id,
            'name' => $productData->name,
            'description' => $productData->description ?? '',
            'unitPrice' => (float) $productData->unitPrice,
            'category' => $productData->category ?? 'General',
            'status' => $productData->status ?? 'active',
            'stock' => (int) $productData->stock,
        ], 'Product inventory updated successfully');
    }

    /**
     * Delete a product
     */
    public function deleteProduct(Request $request, $unitId, $productId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $product = Product::where('id', $productId)
            ->where('franchise_id', $unit->franchise_id)
            ->whereHas('units', function ($query) use ($unit) {
                $query->where('units.id', $unit->id);
            })
            ->first();
        if (! $product) {
            return $this->notFoundResponse('Product not found');
        }

        $product->delete();

        return $this->successResponse(null, 'Product deleted successfully');
    }

    /**
     * Create a new review
     */
    public function createReview(StoreReviewRequest $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $validated = $request->validated();

        $review = $unit->reviews()->create([
            'customer_name' => $validated['customerName'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'review_date' => $validated['date'] ?? now(),
        ]);

        return $this->successResponse([
            'id' => $review->id,
            'customerName' => $review->customer_name,
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'date' => $review->review_date ?? $review->created_at->format('Y-m-d'),
            'sentiment' => $review->rating >= 4 ? 'positive' : ($review->rating >= 3 ? 'neutral' : 'negative'),
        ], 'Review created successfully', 201);
    }

    /**
     * Update a review
     */
    public function updateReview(UpdateReviewRequest $request, $unitId, $reviewId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $review = $unit->reviews()->where('id', $reviewId)->first();
        if (! $review) {
            return $this->notFoundResponse('Review not found');
        }

        $validated = $request->validated();

        $review->update([
            'customer_name' => $validated['customerName'] ?? $review->customer_name,
            'customer_email' => $validated['customerEmail'] ?? $review->customer_email,
            'rating' => $validated['rating'] ?? $review->rating,
            'comment' => $validated['comment'] ?? $review->comment,
            'review_date' => $validated['date'] ?? $review->review_date,
        ]);

        return $this->successResponse([
            'id' => $review->id,
            'customerName' => $review->customer_name,
            'customerEmail' => $review->customer_email ?? '',
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'date' => $review->review_date ? $review->review_date->format('Y-m-d') : $review->created_at->format('Y-m-d'),
            'sentiment' => $review->rating >= 4 ? 'positive' : ($review->rating >= 3 ? 'neutral' : 'negative'),
        ], 'Review updated successfully');
    }

    /**
     * Delete a review
     */
    public function deleteReview(Request $request, $unitId, $reviewId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $review = $unit->reviews()->where('id', $reviewId)->first();
        if (! $review) {
            return $this->notFoundResponse('Review not found');
        }

        $review->delete();

        return $this->successResponse(null, 'Review deleted successfully');
    }

    /**
     * Create a new document
     */
    public function createDocument(Request $request, $unitId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
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

        return $this->successResponse($newDocument, 'Document created successfully', 201);
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
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $documents = $unit->documents ?? [];
        $docIndex = $documentId - 1;

        if (! isset($documents[$docIndex])) {
            return $this->notFoundResponse('Document not found');
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

        return $this->successResponse($updatedDocument, 'Document updated successfully');
    }

    /**
     * Delete a document
     */
    public function deleteDocument(Request $request, $unitId, $documentId): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $documents = $unit->documents ?? [];
        $docIndex = $documentId - 1;

        if (! isset($documents[$docIndex])) {
            return $this->notFoundResponse('Document not found');
        }

        unset($documents[$docIndex]);
        $documents = array_values($documents); // Re-index array
        $unit->update(['documents' => $documents]);

        return $this->successResponse(null, 'Document deleted successfully');
    }

    /**
     * Download a document
     */
    public function downloadDocument(Request $request, $unitId, $documentId)
    {
        $user = $request->user();
        $unit = Unit::where('id', $unitId)->where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('Unit not found or access denied');
        }

        $documents = $unit->documents ?? [];
        $docIndex = $documentId - 1;

        if (! isset($documents[$docIndex])) {
            return $this->notFoundResponse('Document not found');
        }

        $document = $documents[$docIndex];

        // Check if filePath exists in the document metadata
        if (isset($document['filePath']) && Storage::exists($document['filePath'])) {
            // File exists - return it for download
            return Storage::download($document['filePath'], $document['fileName']);
        }

        // File doesn't exist - return error
        return $this->notFoundResponse('File not found in storage. The document may have been uploaded before file storage was implemented.');
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
            'nationality' => $unit->nationality ?? 'Saudi Arabia',
            'royaltyPercentage' => $unit->franchise->royalty_percentage ?? 0,
            'contractStartDate' => $unit->lease_start_date ?? null,
            'renewalDate' => $unit->lease_end_date ?? null,
            'status' => $unit->status ?? 'active',
        ];
    }

    /**
     * Get all tasks for the current franchisee (unit tasks + assigned tasks) - bidirectional
     */
    public function myTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        // Base query - get all tasks related to this unit or user
        $query = \App\Models\Task::with(['assignedTo', 'createdBy', 'unit', 'franchise'])
            ->where(function ($q) use ($unit, $user) {
                $q->where('unit_id', $unit->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);
            });

        // Apply filter parameter for bidirectional management
        if ($request->has('filter')) {
            $filter = $request->filter;
            if ($filter === 'assigned') {
                // Only tasks assigned TO the franchisee
                $query->where('assigned_to', $user->id);
            } elseif ($filter === 'created') {
                // Only tasks created BY the franchisee (potentially for franchisor)
                $query->where('created_by', $user->id);
            }
            // 'all' or no filter shows all tasks
        }

        // Apply status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query
            ->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'type' => $task->type,
                    'category' => $task->category,
                    'assigned_to' => $task->assignedTo ? [
                        'id' => $task->assignedTo->id,
                        'name' => $task->assignedTo->name,
                        'role' => $task->assignedTo->role,
                    ] : null,
                    'created_by' => $task->createdBy ? [
                        'id' => $task->createdBy->id,
                        'name' => $task->createdBy->name,
                        'role' => $task->createdBy->role,
                    ] : null,
                    'unit' => $task->unit ? [
                        'id' => $task->unit->id,
                        'unit_name' => $task->unit->unit_name,
                    ] : null,
                    'started_at' => $task->started_at?->toISOString(),
                    'due_date' => $task->due_date?->toISOString(),
                    'priority' => $task->priority,
                    'status' => $task->status,
                    'estimated_hours' => $task->estimated_hours,
                    'actual_hours' => $task->actual_hours,
                    'created_at' => $task->created_at->toISOString(),
                    'updated_at' => $task->updated_at->toISOString(),
                ];
            });

        return $this->successResponse(['data' => $tasks], 'Tasks retrieved successfully');
    }

    /**
     * Update task status for my tasks
     */
    public function updateMyTaskStatus(UpdateTaskStatusRequest $request, $taskId): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        $task = \App\Models\Task::where(function ($query) use ($unit, $user) {
            $query->where('unit_id', $unit->id)
                ->orWhere('assigned_to', $user->id)
                ->orWhere('created_by', $user->id);
        })->find($taskId);

        if (! $task) {
                return $this->notFoundResponse('Task not found or you do not have permission to update it');
        }

        $task->update(['status' => $validated['status']]);

        // Update completed_at timestamp if status is completed
        if ($validated['status'] === 'completed' && ! $task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        // Update started_at timestamp if status is in_progress and not already started
        if ($validated['status'] === 'in_progress' && ! $task->started_at) {
            $task->update(['started_at' => now()]);
        }

        return $this->successResponse([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'category' => $task->category,
            'assignedTo' => $task->assignedTo ? $task->assignedTo->name : 'Unassigned',
            'unitName' => $task->unit ? $task->unit->branch_name : 'N/A',
            'startDate' => $task->started_at ? $task->started_at->format('Y-m-d') : $task->created_at->format('Y-m-d'),
            'dueDate' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
            'priority' => $task->priority,
            'status' => $task->status,
        ], 'Task status updated successfully');
    }

    /**
     * Get performance management data
     */
    public function performanceManagement(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        // Get product performance data
        $productPerformance = $this->getProductPerformance($unit);

        // Get royalty data
        $royaltyData = $this->getRoyaltyData($unit);

        // Get tasks overview
        $tasksOverview = $this->getTasksOverview($unit, $user);

        // Get customer satisfaction
        $customerSatisfaction = $this->getCustomerSatisfaction($unit);

        return $this->successResponse([
            'productPerformance' => $productPerformance,
            'royalty' => $royaltyData,
            'tasksOverview' => $tasksOverview,
            'customerSatisfaction' => $customerSatisfaction,
        ], 'Performance management data retrieved successfully');
    }

    /**
     * Get product performance data
     */
    private function getProductPerformance(Unit $unit): array
    {
        $currentYear = now()->year;

        // Get monthly sales for each product from revenues
        $revenues = \App\Models\Revenue::where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('period_year', $currentYear)
            ->whereNotNull('line_items')
            ->get();

        // Aggregate product sales by month
        $productSales = [];
        foreach ($revenues as $revenue) {
            if ($revenue->line_items) {
                foreach ($revenue->line_items as $item) {
                    $productId = $item['product_id'] ?? null;
                    if ($productId) {
                        $month = $revenue->period_month;
                        if (! isset($productSales[$productId])) {
                            $productSales[$productId] = array_fill(1, 12, 0);
                        }
                        $productSales[$productId][$month] += $item['quantity'] ?? 0;
                    }
                }
            }
        }

        // Find top and low performing products
        $productTotals = [];
        foreach ($productSales as $productId => $months) {
            $productTotals[$productId] = array_sum($months);
        }

        arsort($productTotals);
        $topProductId = array_key_first($productTotals);
        $lowProductId = array_key_last($productTotals);

        $topProductData = $topProductId ? array_values($productSales[$topProductId]) : array_fill(0, 12, 0);
        $lowProductData = $lowProductId ? array_values($productSales[$lowProductId]) : array_fill(0, 12, 0);

        return [
            'topPerformingProductData' => $topProductData,
            'lowPerformingProductData' => $lowProductData,
        ];
    }

    /**
     * Get royalty data
     */
    private function getRoyaltyData(Unit $unit): array
    {
        $royalties = \App\Models\Royalty::where('unit_id', $unit->id)
            ->orderBy('period_start_date', 'desc')
            ->take(4)
            ->get();

        $royaltyAmount = $royalties->first()->total_amount ?? 0;
        $royaltyPhaseData = $royalties->reverse()->pluck('total_amount')->toArray();

        // Pad with zeros if less than 4 phases
        while (count($royaltyPhaseData) < 4) {
            array_unshift($royaltyPhaseData, 0);
        }

        return [
            'amount' => $royaltyAmount,
            'phaseData' => $royaltyPhaseData,
        ];
    }

    /**
     * Get tasks overview
     */
    private function getTasksOverview(Unit $unit, $user): array
    {
        $tasks = \App\Models\Task::where(function ($query) use ($unit, $user) {
            $query->where('unit_id', $unit->id)
                ->orWhere('assigned_to', $user->id)
                ->orWhere('created_by', $user->id);
        })->get();

        $total = $tasks->count();
        $completed = $tasks->where('status', 'completed')->count();
        $inProgress = $tasks->where('status', 'in_progress')->count();

        $today = now();
        $due = $tasks->filter(function ($task) {
            return $task->due_date && $task->due_date->isPast() && ! in_array($task->status, ['completed', 'cancelled']);
        })->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'inProgress' => $inProgress,
            'due' => $due,
        ];
    }

    /**
     * Get customer satisfaction data
     */
    private function getCustomerSatisfaction(Unit $unit): array
    {
        $reviews = \App\Models\Review::where('unit_id', $unit->id)
            ->where('status', 'published')
            ->get();

        $totalReviews = $reviews->count();

        if ($totalReviews === 0) {
            return [
                'score' => 0,
                'users' => 0,
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
            ];
        }

        $averageRating = $reviews->avg('rating');
        $score = round(($averageRating / 5) * 100);

        $positive = $reviews->where('sentiment', 'positive')->count();
        $neutral = $reviews->where('sentiment', 'neutral')->count();
        $negative = $reviews->where('sentiment', 'negative')->count();

        $positivePercent = round(($positive / $totalReviews) * 100);
        $neutralPercent = round(($neutral / $totalReviews) * 100);
        $negativePercent = round(($negative / $totalReviews) * 100);

        return [
            'score' => $score,
            'users' => $totalReviews,
            'positive' => $positivePercent,
            'neutral' => $neutralPercent,
            'negative' => $negativePercent,
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

    /**
     * Get financial overview data (sales, expenses, profit)
     */
    public function financialOverview(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        // Get sales data from revenues
        $salesData = $this->getSalesData($unit);

        // Get expense data from transactions
        $expenseData = $this->getExpenseData($unit);

        // Calculate profit data
        $profitData = $this->calculateProfitData($salesData, $expenseData);

        // Calculate totals
        $totalSales = collect($salesData)->sum('sale');
        $totalExpenses = collect($expenseData)->sum('amount');
        $totalProfit = $totalSales - $totalExpenses;

        return $this->successResponse([
            'sales' => $salesData,
            'expenses' => $expenseData,
            'profit' => $profitData,
            'totals' => [
                'sales' => $totalSales,
                'expenses' => $totalExpenses,
                'profit' => $totalProfit,
            ],
        ], 'Financial overview data retrieved successfully');
    }

    /**
     * Get sales data from revenues
     */
    private function getSalesData(Unit $unit): array
    {
        $revenues = Revenue::where('unit_id', $unit->id)
            ->where('type', 'sales')
            ->where('revenue_date', '>=', now()->subMonths(3))
            ->orderBy('revenue_date', 'desc')
            ->get();

        $salesData = [];

        foreach ($revenues as $revenue) {
            if ($revenue->line_items && is_array($revenue->line_items)) {
                foreach ($revenue->line_items as $item) {
                    $salesData[] = [
                        'id' => $revenue->id . '-' . ($item['product_id'] ?? 'item'),
                        'product' => $item['product_name'] ?? $item['product'] ?? 'Unknown Product',
                        'dateOfSale' => $revenue->revenue_date->format('Y-m-d'),
                        'unitPrice' => (float) ($item['price'] ?? 0),
                        'quantitySold' => (int) ($item['quantity'] ?? 0),
                        'sale' => (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0),
                    ];
                }
            }
        }

        return $salesData;
    }

    /**
     * Get expense data from transactions
     */
    private function getExpenseData(Unit $unit): array
    {
        $transactions = \App\Models\Transaction::where('unit_id', $unit->id)
            ->where('type', 'expense')
            ->where('transaction_date', '>=', now()->subMonths(3))
            ->orderBy('transaction_date', 'desc')
            ->get();

        return $transactions->map(function ($transaction) {
            return [
                'id' => (string) $transaction->id,
                'expenseCategory' => $this->mapEnumToExpenseCategory($transaction->category ?? 'other'),
                'dateOfExpense' => $transaction->transaction_date->format('Y-m-d'),
                'amount' => (float) $transaction->amount,
                'description' => $transaction->description ?? '',
            ];
        })->toArray();
    }

    /**
     * Calculate profit data by aggregating sales and expenses by date
     */
    private function calculateProfitData(array $salesData, array $expenseData): array
    {
        // Group sales by date
        $salesByDate = collect($salesData)->groupBy('dateOfSale')->map(function ($items) {
            return collect($items)->sum('sale');
        });

        // Group expenses by date
        $expensesByDate = collect($expenseData)->groupBy('dateOfExpense')->map(function ($items) {
            return collect($items)->sum('amount');
        });

        // Get all unique dates
        $allDates = $salesByDate->keys()->merge($expensesByDate->keys())->unique()->sort()->reverse();

        $profitData = [];
        foreach ($allDates as $date) {
            $sales = $salesByDate->get($date, 0);
            $expenses = $expensesByDate->get($date, 0);

            $profitData[] = [
                'id' => $date,
                'date' => $date,
                'totalSales' => $sales,
                'totalExpenses' => $expenses,
                'profit' => $sales - $expenses,
            ];
        }

        return array_values($profitData);
    }

    /**
     * Map frontend expense category to database enum value
     */
    private function mapExpenseCategoryToEnum(string $category): string
    {
        $categoryMap = [
            'Food Supplies' => 'cost_of_goods',
            'Utilities' => 'utilities',
            'Staff Wages' => 'labor',
            'Marketing' => 'marketing',
            'Equipment Maintenance' => 'equipment',
            'Rent' => 'rent',
            'Insurance' => 'insurance',
            'Cleaning Supplies' => 'supplies',
            'Office Supplies' => 'supplies',
            'Transportation' => 'other',
        ];

        return $categoryMap[$category] ?? 'other';
    }

    /**
     * Map database enum value to frontend expense category
     */
    private function mapEnumToExpenseCategory(string $enumValue): string
    {
        $reverseMap = [
            'cost_of_goods' => 'Food Supplies',
            'utilities' => 'Utilities',
            'labor' => 'Staff Wages',
            'marketing' => 'Marketing',
            'equipment' => 'Equipment Maintenance',
            'rent' => 'Rent',
            'insurance' => 'Insurance',
            'supplies' => 'Office Supplies',
            'other' => 'Transportation',
        ];

        return $reverseMap[$enumValue] ?? 'Other';
    }

    /**
     * Add new financial data (sale or expense)
     */
    public function storeFinancialData(StoreFinancialDataRequest $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        $validated = $request->validated();

        if ($validated['category'] === 'sales') {
            // Find the product
            $product = Product::where('franchise_id', $unit->franchise_id)
                ->where('name', $validated['product'])
                ->first();

            if (! $product) {
                return $this->notFoundResponse('Product not found');
            }

            // Check inventory
            $inventory = \App\Models\Inventory::where('unit_id', $unit->id)
                ->where('product_id', $product->id)
                ->first();

            if (! $inventory) {
                return $this->errorResponse('Product not available in your unit inventory', null, 400);
            }

            $quantity = (int) $validated['quantitySold'];

            // Validate stock availability
            if ($inventory->quantity < $quantity) {
                return $this->errorResponse("Insufficient stock. Only {$inventory->quantity} units available", null, 400);
            }

            $unitPrice = (float) $product->unit_price;
            $saleAmount = $unitPrice * $quantity;

            // Create revenue with line item
            $revenue = Revenue::create([
                'revenue_number' => 'REV' . now()->timestamp . rand(1000, 9999),
                'franchise_id' => $unit->franchise_id,
                'unit_id' => $unit->id,
                'user_id' => $user->id,
                'type' => 'sales',
                'category' => 'product_sales',
                'amount' => $saleAmount,
                'net_amount' => $saleAmount,
                'currency' => 'SAR',
                'description' => 'Product sale: ' . $validated['product'],
                'revenue_date' => $validated['date'],
                'period_year' => date('Y', strtotime($validated['date'])),
                'period_month' => date('n', strtotime($validated['date'])),
                'status' => 'verified',
                'payment_status' => 'completed',
                'line_items' => [
                    [
                        'product_id' => $product?->id,
                        'product_name' => $validated['product'],
                        'quantity' => $quantity,
                        'price' => $unitPrice,
                    ],
                ],
            ]);

            // Update inventory - reduce stock quantity
            $inventory->decrement('quantity', $quantity);
            $inventory->refresh(); // Refresh to get the updated quantity

            return $this->successResponse([
                'id' => $revenue->id . '-item',
                'product' => $validated['product'],
                'dateOfSale' => $validated['date'],
                'unitPrice' => $unitPrice,
                'quantitySold' => $quantity,
                'sale' => $saleAmount,
                'remainingStock' => $inventory->quantity,
            ], 'Sales data added successfully', 201);
        } else {
            // Create expense transaction
            $transaction = \App\Models\Transaction::create([
                'transaction_number' => 'EXP' . now()->timestamp . rand(1000, 9999),
                'franchise_id' => $unit->franchise_id,
                'unit_id' => $unit->id,
                'user_id' => $user->id,
                'type' => 'expense',
                'category' => $this->mapExpenseCategoryToEnum($validated['expenseCategory']),
                'amount' => $validated['amount'],
                'currency' => 'SAR',
                'description' => $validated['description'] ?? '',
                'transaction_date' => $validated['date'],
                'status' => 'completed',
            ]);

            return $this->successResponse([
                'id' => (string) $transaction->id,
                'expenseCategory' => $validated['expenseCategory'], // Return the original user-friendly name
                'dateOfExpense' => $validated['date'],
                'amount' => (float) $validated['amount'],
                'description' => $validated['description'] ?? '',
            ], 'Expense data added successfully', 201);
        }
    }

    /**
     * Update financial data (sale or expense)
     */
    public function updateFinancialData(UpdateFinancialDataRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        $validated = $request->validated();

        if ($validated['category'] === 'sales') {
            // Extract revenue ID from composite ID (format: "revenueId-item")
            $revenueId = explode('-', $id)[0];

            // Find the revenue
            $revenue = Revenue::where('unit_id', $unit->id)
                ->where('id', $revenueId)
                ->first();

            if (! $revenue) {
                return $this->notFoundResponse('Sales record not found');
            }

            // Find the product
            $product = Product::where('franchise_id', $unit->franchise_id)
                ->where('name', $validated['product'])
                ->first();

            if (! $product) {
                return $this->notFoundResponse('Product not found');
            }

            // Check inventory
            $inventory = \App\Models\Inventory::where('unit_id', $unit->id)
                ->where('product_id', $product->id)
                ->first();

            if (! $inventory) {
                return $this->errorResponse('Product not available in your unit inventory', null, 400);
            }

            $newQuantity = (int) $validated['quantitySold'];

            // Get the old quantity from the revenue's line_items
            $oldQuantity = 0;
            if ($revenue->line_items && is_array($revenue->line_items)) {
                foreach ($revenue->line_items as $item) {
                    if (isset($item['product_name']) && $item['product_name'] === $validated['product']) {
                        $oldQuantity = (int) ($item['quantity'] ?? 0);
                        break;
                    }
                }
            }

            // Calculate the difference in quantity
            $quantityDifference = $newQuantity - $oldQuantity;

            // Check if we have enough stock for the increase
            if ($quantityDifference > 0 && $inventory->quantity < $quantityDifference) {
                return $this->errorResponse("Insufficient stock. Only {$inventory->quantity} units available for increase", null, 400);
            }

            $unitPrice = (float) $product->unit_price;
            $saleAmount = $unitPrice * $newQuantity;

            // Update revenue
            $revenue->update([
                'amount' => $saleAmount,
                'revenue_date' => $validated['date'],
                'period_year' => date('Y', strtotime($validated['date'])),
                'period_month' => date('n', strtotime($validated['date'])),
                'line_items' => [
                    [
                        'product_id' => $product->id,
                        'product_name' => $validated['product'],
                        'quantity' => $newQuantity,
                        'price' => $unitPrice,
                    ],
                ],
            ]);

            // Update inventory based on the difference
            // If quantityDifference is positive, we're selling more (decrease inventory)
            // If quantityDifference is negative, we're selling less (increase inventory)
            if ($quantityDifference != 0) {
                if ($quantityDifference > 0) {
                    $inventory->decrement('quantity', $quantityDifference);
                } else {
                    $inventory->increment('quantity', abs($quantityDifference));
                }
                $inventory->refresh();
            }

            return $this->successResponse([
                'id' => $revenue->id . '-item',
                'product' => $validated['product'],
                'dateOfSale' => $validated['date'],
                'unitPrice' => $unitPrice,
                'quantitySold' => $newQuantity,
                'sale' => $saleAmount,
                'remainingStock' => $inventory->quantity,
            ], 'Sales data updated successfully');
        } else {
            // Update expense transaction
            $transaction = \App\Models\Transaction::where('unit_id', $unit->id)
                ->where('id', $id)
                ->where('type', 'expense')
                ->first();

            if (! $transaction) {
                return $this->notFoundResponse('Expense record not found');
            }

            $transaction->update([
                'category' => $this->mapExpenseCategoryToEnum($validated['expenseCategory']),
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? '',
                'transaction_date' => $validated['date'],
            ]);

            return $this->successResponse([
                'id' => (string) $transaction->id,
                'expenseCategory' => $validated['expenseCategory'],
                'dateOfExpense' => $validated['date'],
                'amount' => (float) $validated['amount'],
                'description' => $validated['description'] ?? '',
            ], 'Expense data updated successfully');
        }
    }

    /**
     * Delete financial data
     */
    public function deleteFinancialData(Request $request): JsonResponse
    {
        $user = $request->user();
        $unit = Unit::where('franchisee_id', $user->id)->first();

        if (! $unit) {
            return $this->notFoundResponse('No unit found for current user');
        }

        $validated = $request->validate([
            'category' => 'required|in:sales,expense,profit',
            'ids' => 'required|array',
            'ids.*' => 'required|string',
        ]);

        $deletedCount = 0;

        if ($validated['category'] === 'expense') {
            // Delete transactions
            $deletedCount = \App\Models\Transaction::where('unit_id', $unit->id)
                ->whereIn('id', $validated['ids'])
                ->delete();
        } elseif ($validated['category'] === 'sales') {
            // Delete revenues (extract revenue IDs from composite IDs)
            $revenueIds = collect($validated['ids'])->map(function ($id) {
                return explode('-', $id)[0];
            })->unique()->toArray();

            $deletedCount = Revenue::where('unit_id', $unit->id)
                ->whereIn('id', $revenueIds)
                ->delete();
        }
        // Note: Profit records are calculated, not stored, so no deletion needed

        return $this->successResponse(null, "$deletedCount record(s) deleted successfully");
    }
}
