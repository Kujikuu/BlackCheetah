# Financial Controller Implementation Guide

## Complete FinancialController Code

This is the complete implementation for `app/Http/Controllers/Api/FinancialController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\Royalty;
use App\Models\Transaction;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FinancialController extends Controller
{
    /**
     * Get chart data for financial overview
     */
    public function charts(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'period' => 'required|in:daily,monthly,yearly',
                'unit_id' => 'nullable|exists:units,id',
                'year' => 'nullable|integer|min:2020|max:2030',
                'month' => 'nullable|integer|min:1|max:12',
            ]);

            $period = $validated['period'];
            $unitId = $validated['unit_id'] ?? null;
            $year = $validated['year'] ?? Carbon::now()->year;
            $month = $validated['month'] ?? Carbon::now()->month;

            // Get date range based on period
            $dateRange = $this->getDateRange($period, $year, $month);
            
            // Base query for revenues (sales)
            $revenueQuery = Revenue::where('franchise_id', $franchise->id)
                ->whereBetween('revenue_date', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'verified');

            // Base query for expenses
            $expenseQuery = Transaction::where('franchise_id', $franchise->id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'completed');

            // Base query for royalties
            $royaltyQuery = Royalty::where('franchise_id', $franchise->id)
                ->whereBetween('period_start_date', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'paid');

            // Filter by unit if specified
            if ($unitId) {
                $revenueQuery->where('unit_id', $unitId);
                $expenseQuery->where('unit_id', $unitId);
                $royaltyQuery->where('unit_id', $unitId);
            }

            // Get grouped data
            $salesData = $this->getGroupedFinancialData($revenueQuery, 'revenue_date', 'net_amount', $period, $dateRange);
            $expensesData = $this->getGroupedFinancialData($expenseQuery, 'transaction_date', 'amount', $period, $dateRange);
            $royaltiesData = $this->getGroupedFinancialData($royaltyQuery, 'period_start_date', 'total_amount', $period, $dateRange);

            // Calculate profit
            $profitData = [];
            foreach ($salesData as $key => $value) {
                $profitData[$key] = $value - ($expensesData[$key] ?? 0) - ($royaltiesData[$key] ?? 0);
            }

            // Format for chart
            $categories = array_keys($salesData);
            $chartData = [
                'categories' => $categories,
                'series' => [
                    ['name' => 'Sales', 'data' => array_values($salesData)],
                    ['name' => 'Expenses', 'data' => array_values($expensesData)],
                    ['name' => 'Royalties', 'data' => array_values($royaltiesData)],
                    ['name' => 'Profit', 'data' => array_values($profitData)],
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $chartData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chart data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get financial statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'period' => 'required|in:daily,monthly,yearly',
                'unit_id' => 'nullable|exists:units,id',
                'year' => 'nullable|integer|min:2020|max:2030',
                'month' => 'nullable|integer|min:1|max:12',
            ]);

            $period = $validated['period'];
            $unitId = $validated['unit_id'] ?? null;
            $year = $validated['year'] ?? Carbon::now()->year;
            $month = $validated['month'] ?? Carbon::now()->month;

            // Get date range
            $dateRange = $this->getDateRange($period, $year, $month);
            
            // Previous period for comparison
            $previousDateRange = $this->getPreviousDateRange($period, $year, $month);

            // Current period totals
            $totalSales = $this->getTotalRevenue($franchise->id, $dateRange['start'], $dateRange['end'], $unitId);
            $totalExpenses = $this->getTotalExpenses($franchise->id, $dateRange['start'], $dateRange['end'], $unitId);
            $totalRoyalties = $this->getTotalRoyalties($franchise->id, $dateRange['start'], $dateRange['end'], $unitId);
            $totalProfit = $totalSales - $totalExpenses - $totalRoyalties;

            // Previous period totals for change calculation
            $previousSales = $this->getTotalRevenue($franchise->id, $previousDateRange['start'], $previousDateRange['end'], $unitId);
            $previousExpenses = $this->getTotalExpenses($franchise->id, $previousDateRange['start'], $previousDateRange['end'], $unitId);
            $previousRoyalties = $this->getTotalRoyalties($franchise->id, $previousDateRange['start'], $previousDateRange['end'], $unitId);
            $previousProfit = $previousSales - $previousExpenses - $previousRoyalties;

            // Calculate percentage changes
            $salesChange = $previousSales > 0 ? (($totalSales - $previousSales) / $previousSales) * 100 : 0;
            $expensesChange = $previousExpenses > 0 ? (($totalExpenses - $previousExpenses) / $previousExpenses) * 100 : 0;
            $profitChange = $previousProfit > 0 ? (($totalProfit - $previousProfit) / $previousProfit) * 100 : 0;

            $statistics = [
                'totalSales' => $totalSales,
                'totalExpenses' => $totalExpenses,
                'totalProfit' => $totalProfit,
                'totalRoyalties' => $totalRoyalties,
                'period' => $period,
                'change' => [
                    'sales' => round($salesChange, 2),
                    'expenses' => round($expensesChange, 2),
                    'profit' => round($profitChange, 2),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sales data with pagination
     */
    public function sales(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'period' => 'required|in:daily,monthly,yearly',
                'unit_id' => 'nullable|exists:units,id',
                'year' => 'nullable|integer|min:2020|max:2030',
                'month' => 'nullable|integer|min:1|max:12',
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            $period = $validated['period'];
            $unitId = $validated['unit_id'] ?? null;
            $year = $validated['year'] ?? Carbon::now()->year;
            $month = $validated['month'] ?? Carbon::now()->month;
            $search = $validated['search'] ?? null;
            $perPage = $validated['per_page'] ?? 10;

            // Get date range
            $dateRange = $this->getDateRange($period, $year, $month);

            $query = Revenue::where('franchise_id', $franchise->id)
                ->where('type', 'sales')
                ->where('status', 'verified')
                ->whereBetween('revenue_date', [$dateRange['start'], $dateRange['end']])
                ->with(['unit', 'user']);

            // Filter by unit if specified
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }

            // Apply search
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('revenue_number', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%");
                });
            }

            // Order by date descending
            $query->orderBy('revenue_date', 'desc');

            // Paginate
            $sales = $query->paginate($perPage);

            // Transform data for frontend
            $transformedSales = $sales->getCollection()->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'product' => $sale->description,
                    'unitPrice' => $sale->amount,
                    'quantity' => 1, // This would need to be adjusted based on your data structure
                    'sale' => $sale->net_amount,
                    'date' => $sale->revenue_date->format('Y-m-d'),
                    'unit' => $sale->unit ? $sale->unit->unit_name : 'N/A',
                    'revenue_number' => $sale->revenue_number,
                    'customer_name' => $sale->customer_name,
                ];
            });

            $sales->setCollection($transformedSales);

            return response()->json([
                'success' => true,
                'data' => $sales,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new sales record
     */
    public function storeSale(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'product' => 'required|string|max:255',
                'dateOfSale' => 'required|date',
                'unitPrice' => 'required|numeric|min:0',
                'quantitySold' => 'required|integer|min:1',
                'unit_id' => 'nullable|exists:units,id',
                'customer_name' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email|max:255',
                'payment_method' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:500',
            ]);

            // Calculate total amount
            $totalAmount = $validated['unitPrice'] * $validated['quantitySold'];

            $revenue = Revenue::create([
                'franchise_id' => $franchise->id,
                'unit_id' => $validated['unit_id'] ?? null,
                'user_id' => $user->id,
                'type' => 'sales',
                'category' => 'product_sales',
                'amount' => $totalAmount,
                'currency' => 'SAR',
                'description' => $validated['product'],
                'revenue_date' => $validated['dateOfSale'],
                'period_year' => Carbon::parse($validated['dateOfSale'])->year,
                'period_month' => Carbon::parse($validated['dateOfSale'])->month,
                'source' => 'manual_entry',
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_status' => 'completed',
                'net_amount' => $totalAmount,
                'status' => 'verified',
                'notes' => $validated['notes'] ?? null,
                'verified_by' => $user->id,
                'verified_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $revenue->load(['unit', 'user']),
                'message' => 'Sales record created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sales record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get expenses data with pagination
     */
    public function expenses(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'period' => 'required|in:daily,monthly,yearly',
                'unit_id' => 'nullable|exists:units,id',
                'year' => 'nullable|integer|min:2020|max:2030',
                'month' => 'nullable|integer|min:1|max:12',
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            $period = $validated['period'];
            $unitId = $validated['unit_id'] ?? null;
            $year = $validated['year'] ?? Carbon::now()->year;
            $month = $validated['month'] ?? Carbon::now()->month;
            $search = $validated['search'] ?? null;
            $perPage = $validated['per_page'] ?? 10;

            // Get date range
            $dateRange = $this->getDateRange($period, $year, $month);

            $query = Transaction::where('franchise_id', $franchise->id)
                ->where('type', 'expense')
                ->where('status', 'completed')
                ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
                ->with(['unit', 'user']);

            // Filter by unit if specified
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }

            // Apply search
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('transaction_number', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            }

            // Order by date descending
            $query->orderBy('transaction_date', 'desc');

            // Paginate
            $expenses = $query->paginate($perPage);

            // Transform data for frontend
            $transformedExpenses = $expenses->getCollection()->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'expenseCategory' => $expense->category,
                    'amount' => $expense->amount,
                    'description' => $expense->description,
                    'date' => $expense->transaction_date->format('Y-m-d'),
                    'unit' => $expense->unit ? $expense->unit->unit_name : 'N/A',
                    'transaction_number' => $expense->transaction_number,
                    'payment_method' => $expense->payment_method,
                    'vendor_customer' => $expense->vendor_customer,
                ];
            });

            $expenses->setCollection($transformedExpenses);

            return response()->json([
                'success' => true,
                'data' => $expenses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expenses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new expense record
     */
    public function storeExpense(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'expenseCategory' => 'required|string|max:100',
                'dateOfExpense' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'description' => 'required|string|max:500',
                'unit_id' => 'nullable|exists:units,id',
                'vendor_customer' => 'nullable|string|max:255',
                'payment_method' => 'nullable|string|max:50',
                'reference_number' => 'nullable|string|max:100',
                'notes' => 'nullable|string|max:500',
            ]);

            $transaction = Transaction::create([
                'type' => 'expense',
                'category' => $validated['expenseCategory'],
                'amount' => $validated['amount'],
                'currency' => 'SAR',
                'description' => $validated['description'],
                'transaction_date' => $validated['dateOfExpense'],
                'franchise_id' => $franchise->id,
                'unit_id' => $validated['unit_id'] ?? null,
                'user_id' => $user->id,
                'status' => 'completed',
                'payment_method' => $validated['payment_method'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'vendor_customer' => $validated['vendor_customer'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'data' => $transaction->load(['unit', 'user']),
                'message' => 'Expense record created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create expense record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get profit data with pagination
     */
    public function profit(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'period' => 'required|in:daily,monthly,yearly',
                'unit_id' => 'nullable|exists:units,id',
                'year' => 'nullable|integer|min:2020|max:2030',
                'month' => 'nullable|integer|min:1|max:12',
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            $period = $validated['period'];
            $unitId = $validated['unit_id'] ?? null;
            $year = $validated['year'] ?? Carbon::now()->year;
            $month = $validated['month'] ?? Carbon::now()->month;
            $search = $validated['search'] ?? null;
            $perPage = $validated['per_page'] ?? 10;

            // Get date range
            $dateRange = $this->getDateRange($period, $year, $month);

            // Get profit data by grouping dates
            $profitQuery = DB::table(function ($query) use ($franchise, $dateRange, $unitId) {
                // Sales subquery
                $query->selectRaw('DATE(revenue_date) as date, SUM(net_amount) as total_sales')
                    ->from('revenues')
                    ->where('franchise_id', $franchise->id)
                    ->where('type', 'sales')
                    ->where('status', 'verified')
                    ->whereBetween('revenue_date', [$dateRange['start'], $dateRange['end']]);
                
                if ($unitId) {
                    $query->where('unit_id', $unitId);
                }
                
                $query->groupBy('date');
            })->leftJoinSub(function ($query) use ($franchise, $dateRange, $unitId) {
                // Expenses subquery
                $query->selectRaw('DATE(transaction_date) as date, SUM(amount) as total_expenses')
                    ->from('transactions')
                    ->where('franchise_id', $franchise->id)
                    ->where('type', 'expense')
                    ->where('status', 'completed')
                    ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']]);
                
                if ($unitId) {
                    $query->where('unit_id', $unitId);
                }
                
                $query->groupBy('date');
            }, 'expenses', 'revenues.date', '=', 'expenses.date')
            ->leftJoinSub(function ($query) use ($franchise, $dateRange, $unitId) {
                // Royalties subquery
                $query->selectRaw('period_start_date as date, SUM(total_amount) as total_royalties')
                    ->from('royalties')
                    ->where('franchise_id', $franchise->id)
                    ->where('status', 'paid')
                    ->whereBetween('period_start_date', [$dateRange['start'], $dateRange['end']]);
                
                if ($unitId) {
                    $query->where('unit_id', $unitId);
                }
                
                $query->groupBy('date');
            }, 'royalties', 'revenues.date', '=', 'royalties.date')
            ->selectRaw('
                COALESCE(revenues.date, expenses.date, royalties.date) as date,
                COALESCE(revenues.total_sales, 0) as total_sales,
                COALESCE(expenses.total_expenses, 0) as total_expenses,
                COALESCE(royalties.total_royalties, 0) as total_royalties,
                (COALESCE(revenues.total_sales, 0) - COALESCE(expenses.total_expenses, 0) - COALESCE(royalties.total_royalties, 0)) as profit
            ')
            ->orderBy('date', 'desc');

            // Apply search
            if ($search) {
                $profitQuery->where('date', 'like', "%{$search}%");
            }

            // Paginate
            $profits = $profitQuery->paginate($perPage);

            // Transform data for frontend
            $transformedProfits = $profits->getCollection()->map(function ($profit) {
                return [
                    'id' => $profit->date,
                    'date' => $profit->date,
                    'totalSales' => $profit->total_sales,
                    'totalExpenses' => $profit->total_expenses,
                    'totalRoyalties' => $profit->total_royalties,
                    'profit' => $profit->profit,
                ];
            });

            $profits->setCollection($transformedProfits);

            return response()->json([
                'success' => true,
                'data' => $profits,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profit data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unit performance data
     */
    public function unitPerformance(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (!$franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'period' => 'required|in:daily,monthly,yearly',
                'year' => 'nullable|integer|min:2020|max:2030',
                'month' => 'nullable|integer|min:1|max:12',
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            $period = $validated['period'];
            $year = $validated['year'] ?? Carbon::now()->year;
            $month = $validated['month'] ?? Carbon::now()->month;
            $search = $validated['search'] ?? null;
            $perPage = $validated['per_page'] ?? 10;

            // Get date range
            $dateRange = $this->getDateRange($period, $year, $month);

            $units = Unit::where('franchise_id', $franchise->id)
                ->with(['franchisee'])
                ->get();

            $unitPerformance = $units->map(function ($unit) use ($dateRange) {
                // Calculate metrics for this unit
                $sales = $this->getTotalRevenue($franchise->id, $dateRange['start'], $dateRange['end'], $unit->id);
                $expenses = $this->getTotalExpenses($franchise->id, $dateRange['start'], $dateRange['end'], $unit->id);
                $royalties = $this->getTotalRoyalties($franchise->id, $dateRange['start'], $dateRange['end'], $unit->id);
                $netSales = $sales - $royalties;
                $profit = $netSales - $expenses;
                $profitMargin = $sales > 0 ? ($profit / $sales) * 100 : 0;

                return [
                    'id' => $unit->id,
                    'name' => $unit->unit_name,
                    'location' => $unit->city . ', ' . $unit->state_province,
                    'sales' => $sales,
                    'expenses' => $expenses,
                    'royalties' => $royalties,
                    'netSales' => $netSales,
                    'profit' => $profit,
                    'profitMargin' => round($profitMargin, 2),
                    'franchisee' => $unit->franchisee ? $unit->franchisee->name : null,
                    'status' => $unit->status,
                ];
            });

            // Apply search
            if ($search) {
                $unitPerformance = $unitPerformance->filter(function ($unit) use ($search) {
                    return stripos($unit['name'], $search) !== false || 
                           stripos($unit['location'], $search) !== false;
                })->values();
            }

            // Sort by profit descending
            $unitPerformance = $unitPerformance->sortByDesc('profit')->values();

            // Paginate manually
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $itemsForPage = $unitPerformance->slice($offset, $perPage)->values();
            $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $itemsForPage,
                $unitPerformance->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $paginated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unit performance data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to get date range based on period
     */
    private function getDateRange(string $period, int $year, int $month): array
    {
        switch ($period) {
            case 'daily':
                return [
                    'start' => Carbon::create($year, $month, 1)->startOfDay(),
                    'end' => Carbon::create($year, $month, 1)->endOfMonth()->endOfDay(),
                ];
            case 'monthly':
                return [
                    'start' => Carbon::create($year, 1, 1)->startOfDay(),
                    'end' => Carbon::create($year, 12, 31)->endOfDay(),
                ];
            case 'yearly':
                return [
                    'start' => Carbon::create(2019, 1, 1)->startOfDay(),
                    'end' => Carbon::create($year, 12, 31)->endOfDay(),
                ];
            default:
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth(),
                ];
        }
    }

    /**
     * Helper method to get previous date range
     */
    private function getPreviousDateRange(string $period, int $year, int $month): array
    {
        switch ($period) {
            case 'daily':
                $previousMonth = Carbon::create($year, $month, 1)->subMonth();
                return [
                    'start' => $previousMonth->startOfDay(),
                    'end' => $previousMonth->endOfMonth()->endOfDay(),
                ];
            case 'monthly':
                return [
                    'start' => Carbon::create($year - 1, 1, 1)->startOfDay(),
                    'end' => Carbon::create($year - 1, 12, 31)->endOfDay(),
                ];
            case 'yearly':
                return [
                    'start' => Carbon::create(2019, 1, 1)->startOfDay(),
                    'end' => Carbon::create($year - 1, 12, 31)->endOfDay(),
                ];
            default:
                return [
                    'start' => Carbon::now()->subMonth()->startOfMonth(),
                    'end' => Carbon::now()->subMonth()->endOfMonth(),
                ];
        }
    }

    /**
     * Helper method to get grouped financial data
     */
    private function getGroupedFinancialData($query, string $dateField, string $valueField, string $period, array $dateRange): array
    {
        $dateFormat = $this->getDateFormat($period);
        
        $data = $query->selectRaw("DATE_FORMAT({$dateField}, '{$dateFormat}') as period, SUM({$valueField}) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period')
            ->toArray();

        // Fill missing periods with zeros
        $completeData = [];
        $periods = $this->generatePeriods($period, $dateRange);
        
        foreach ($periods as $period) {
            $completeData[$period] = $data[$period] ?? 0;
        }

        return $completeData;
    }

    /**
     * Helper method to get date format based on period
     */
    private function getDateFormat(string $period): string
    {
        switch ($period) {
            case 'daily':
                return '%Y-%m-%d';
            case 'monthly':
                return '%Y-%m';
            case 'yearly':
                return '%Y';
            default:
                return '%Y-%m';
        }
    }

    /**
     * Helper method to generate periods
     */
    private function generatePeriods(string $period, array $dateRange): array
    {
        $periods = [];
        $current = Carbon::parse($dateRange['start']);
        $end = Carbon::parse($dateRange['end']);

        switch ($period) {
            case 'daily':
                while ($current <= $end) {
                    $periods[] = $current->format('Y-m-d');
                    $current->addDay();
                }
                break;
            case 'monthly':
                while ($current <= $end) {
                    $periods[] = $current->format('Y-m');
                    $current->addMonth();
                }
                break;
            case 'yearly':
                while ($current <= $end) {
                    $periods[] = $current->format('Y');
                    $current->addYear();
                }
                break;
        }

        return $periods;
    }

    /**
     * Helper method to get total revenue
     */
    private function getTotalRevenue(int $franchiseId, string $startDate, string $endDate, ?int $unitId = null): float
    {
        $query = Revenue::where('franchise_id', $franchiseId)
            ->where('type', 'sales')
            ->where('status', 'verified')
            ->whereBetween('revenue_date', [$startDate, $endDate]);

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        return $query->sum('net_amount');
    }

    /**
     * Helper method to get total expenses
     */
    private function getTotalExpenses(int $franchiseId, string $startDate, string $endDate, ?int $unitId = null): float
    {
        $query = Transaction::where('franchise_id', $franchiseId)
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        return $query->sum('amount');
    }

    /**
     * Helper method to get total royalties
     */
    private function getTotalRoyalties(int $franchiseId, string $startDate, string $endDate, ?int $unitId = null): float
    {
        $query = Royalty::where('franchise_id', $franchiseId)
            ->where('status', 'paid')
            ->whereBetween('period_start_date', [$startDate, $endDate]);

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        return $query->sum('total_amount');
    }
}
```

## API Routes to Add

Add these routes to `routes/api.php` inside the franchisor route group (around line 354):

```php
// Financial Overview Routes
Route::prefix('financial')->group(function () {
    Route::get('charts', [FinancialController::class, 'charts']);
    Route::get('statistics', [FinancialController::class, 'statistics']);
    Route::get('unit-performance', [FinancialController::class, 'unitPerformance']);
    
    // Sales routes
    Route::get('sales', [FinancialController::class, 'sales']);
    Route::post('sales', [FinancialController::class, 'storeSale']);
    Route::put('sales/{id}', [FinancialController::class, 'updateSale']);
    Route::delete('sales/{id}', [FinancialController::class, 'destroySale']);
    
    // Expenses routes
    Route::get('expenses', [FinancialController::class, 'expenses']);
    Route::post('expenses', [FinancialController::class, 'storeExpense']);
    Route::put('expenses/{id}', [FinancialController::class, 'updateExpense']);
    Route::delete('expenses/{id}', [FinancialController::class, 'destroyExpense']);
    
    // Profit routes
    Route::get('profit', [FinancialController::class, 'profit']);
    
    // Import/Export routes (to be implemented later)
    Route::post('import', [FinancialController::class, 'import']);
    Route::get('export', [FinancialController::class, 'export']);
});
```

## Additional Methods to Implement

Add these additional methods to the FinancialController:

```php
/**
 * Update a sales record
 */
public function updateSale(Request $request, $id): JsonResponse
{
    // Implementation for updating sales record
}

/**
 * Delete a sales record
 */
public function destroySale($id): JsonResponse
{
    // Implementation for deleting sales record
}

/**
 * Update an expense record
 */
public function updateExpense(Request $request, $id): JsonResponse
{
    // Implementation for updating expense record
}

/**
 * Delete an expense record
 */
public function destroyExpense($id): JsonResponse
{
    // Implementation for deleting expense record
}

/**
 * Import financial data
 */
public function import(Request $request): JsonResponse
{
    // Implementation for importing data
}

/**
 * Export financial data
 */
public function export(Request $request): JsonResponse
{
    // Implementation for exporting data
}
```

## Testing the Implementation

1. Test the charts endpoint:
   ```
   GET /api/v1/franchisor/financial/charts?period=monthly
   ```

2. Test the statistics endpoint:
   ```
   GET /api/v1/franchisor/financial/statistics?period=monthly
   ```

3. Test the sales endpoint:
   ```
   GET /api/v1/franchisor/financial/sales?period=monthly
   ```

4. Test creating a sales record:
   ```
   POST /api/v1/franchisor/financial/sales
   Body: {
     "product": "Test Product",
     "dateOfSale": "2024-01-15",
     "unitPrice": 100,
     "quantitySold": 5
   }
   ```

This implementation provides a solid foundation for the financial overview backend API. The next step would be to update the frontend to use these endpoints instead of mock data.
