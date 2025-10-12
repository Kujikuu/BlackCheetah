<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\Royalty;
use App\Models\Transaction;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            if (! $franchise) {
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
                ],
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

            if (! $franchise) {
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

            if (! $franchise) {
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
    public function storeSale(StoreSaleRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validated();

            // Calculate total amount
            $totalAmount = $validated['unit_price'] * $validated['quantity'];

            $revenue = Revenue::create([
                'franchise_id' => $franchise->id,
                'unit_id' => $validated['unit_id'] ?? null,
                'user_id' => $user->id,
                'type' => 'sales',
                'category' => 'product_sales',
                'amount' => $totalAmount,
                'currency' => 'SAR',
                'description' => $validated['product'],
                'revenue_date' => $validated['date'],
                'period_year' => Carbon::parse($validated['date'])->year,
                'period_month' => Carbon::parse($validated['date'])->month,
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

            if (! $franchise) {
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
    public function storeExpense(StoreExpenseRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validated();

            $transaction = Transaction::create([
                'type' => 'expense',
                'category' => $validated['expense_category'],
                'amount' => $validated['amount'],
                'currency' => 'SAR',
                'description' => $validated['description'] ?? $validated['expense_category'],
                'transaction_date' => $validated['date'],
                'franchise_id' => $franchise->id,
                'unit_id' => $validated['unit_id'] ?? null,
                'user_id' => $user->id,
                'status' => 'completed',
                'payment_method' => $validated['payment_method'] ?? null,
                'reference_number' => $validated['receipt_number'] ?? null,
                'vendor_customer' => $validated['vendor'] ?? null,
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
     * Update a sales record
     */
    public function updateSale(Request $request, int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $revenue = Revenue::where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->where('type', 'sales')
                ->first();

            if (! $revenue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales record not found',
                ], 404);
            }

            $validated = $request->validate([
                'product' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'unit_price' => 'sometimes|required|numeric|min:0',
                'quantity' => 'sometimes|required|integer|min:1',
                'customer_name' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email|max:255',
                'payment_method' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:500',
            ]);

            // Update revenue record
            $revenue->update([
                'description' => $validated['product'] ?? $revenue->description,
                'revenue_date' => $validated['date'] ?? $revenue->revenue_date,
                'customer_name' => $validated['customer_name'] ?? $revenue->customer_name,
                'customer_email' => $validated['customer_email'] ?? $revenue->customer_email,
                'payment_method' => $validated['payment_method'] ?? $revenue->payment_method,
                'notes' => $validated['notes'] ?? $revenue->notes,
            ]);

            // If unit price or quantity changed, recalculate amount
            if (isset($validated['unit_price']) || isset($validated['quantity'])) {
                $unitPrice = $validated['unit_price'] ?? $revenue->amount;
                $quantity = $validated['quantity'] ?? 1;
                $totalAmount = $unitPrice * $quantity;

                $revenue->update([
                    'amount' => $totalAmount,
                    'net_amount' => $totalAmount,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $revenue->load(['unit', 'user']),
                'message' => 'Sales record updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sales record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a sales record
     */
    public function deleteSale(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $revenue = Revenue::where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->where('type', 'sales')
                ->first();

            if (! $revenue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales record not found',
                ], 404);
            }

            $revenue->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sales record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an expense record
     */
    public function updateExpense(Request $request, int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $transaction = Transaction::where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->where('type', 'expense')
                ->first();

            if (! $transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense record not found',
                ], 404);
            }

            $validated = $request->validate([
                'expense_category' => 'sometimes|required|string|max:100',
                'date' => 'sometimes|required|date',
                'amount' => 'sometimes|required|numeric|min:0',
                'description' => 'sometimes|required|string|max:500',
                'vendor' => 'nullable|string|max:255',
                'payment_method' => 'nullable|string|max:50',
                'receipt_number' => 'nullable|string|max:100',
                'notes' => 'nullable|string|max:500',
            ]);

            $transaction->update([
                'category' => $validated['expense_category'] ?? $transaction->category,
                'amount' => $validated['amount'] ?? $transaction->amount,
                'description' => $validated['description'] ?? $transaction->description,
                'transaction_date' => $validated['date'] ?? $transaction->transaction_date,
                'vendor_customer' => $validated['vendor'] ?? $transaction->vendor_customer,
                'payment_method' => $validated['payment_method'] ?? $transaction->payment_method,
                'reference_number' => $validated['receipt_number'] ?? $transaction->reference_number,
                'notes' => $validated['notes'] ?? $transaction->notes,
            ]);

            return response()->json([
                'success' => true,
                'data' => $transaction->load(['unit', 'user']),
                'message' => 'Expense record updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an expense record
     */
    public function deleteExpense(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $transaction = Transaction::where('franchise_id', $franchise->id)
                ->where('id', $id)
                ->where('type', 'expense')
                ->first();

            if (! $transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense record not found',
                ], 404);
            }

            $transaction->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense record',
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

            if (! $franchise) {
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

            // Simplified approach: Get all dates in the range and calculate profit for each
            $profits = [];
            $current = Carbon::parse($dateRange['start']);
            $end = Carbon::parse($dateRange['end']);

            while ($current <= $end) {
                $dateStr = $current->format('Y-m-d');

                // Calculate sales for this date
                $salesQuery = Revenue::where('franchise_id', $franchise->id)
                    ->where('type', 'sales')
                    ->where('status', 'verified')
                    ->whereDate('revenue_date', $dateStr);

                if ($unitId) {
                    $salesQuery->where('unit_id', $unitId);
                }

                $totalSales = $salesQuery->sum('net_amount');

                // Calculate expenses for this date
                $expensesQuery = Transaction::where('franchise_id', $franchise->id)
                    ->where('type', 'expense')
                    ->where('status', 'completed')
                    ->whereDate('transaction_date', $dateStr);

                if ($unitId) {
                    $expensesQuery->where('unit_id', $unitId);
                }

                $totalExpenses = $expensesQuery->sum('amount');

                // Calculate royalties for this date
                $royaltiesQuery = Royalty::where('franchise_id', $franchise->id)
                    ->where('status', 'paid')
                    ->whereDate('period_start_date', $dateStr);

                if ($unitId) {
                    $royaltiesQuery->where('unit_id', $unitId);
                }

                $totalRoyalties = $royaltiesQuery->sum('total_amount');

                // Calculate profit
                $profit = $totalSales - $totalExpenses - $totalRoyalties;

                $profits[] = [
                    'id' => $dateStr,
                    'date' => $dateStr,
                    'totalSales' => $totalSales,
                    'totalExpenses' => $totalExpenses,
                    'totalRoyalties' => $totalRoyalties,
                    'profit' => $profit,
                ];

                $current->addDay();
            }

            // Apply search if provided
            if ($search) {
                $profits = array_filter($profits, function ($profit) use ($search) {
                    return strpos($profit['date'], $search) !== false;
                });
            }

            // Sort by date descending
            usort($profits, function ($a, $b) {
                return strcmp($b['date'], $a['date']);
            });

            // Convert to collection for pagination
            $profitCollection = collect($profits);

            // Paginate manually
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $itemsForPage = $profitCollection->slice($offset, $perPage)->values();
            $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $itemsForPage,
                $profitCollection->count(),
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

            if (! $franchise) {
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

            $unitPerformance = $units->map(function ($unit) use ($dateRange, $franchise) {
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
                    'location' => $unit->city.', '.$unit->state_province,
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

    /**
     * Import financial data from file
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // Max 10MB
                'category' => 'required|in:sales,expenses',
            ]);

            $file = $request->file('file');
            $category = $validated['category'];

            // Process the file based on category
            $importedCount = 0;
            $errors = [];

            if ($category === 'sales') {
                // Import sales data
                // This is a simplified example - you would use a proper CSV/Excel parser
                $csvData = array_map('str_getcsv', file($file->getPathname()));
                $headers = array_shift($csvData);

                foreach ($csvData as $row) {
                    try {
                        $data = array_combine($headers, $row);

                        Revenue::create([
                            'franchise_id' => $franchise->id,
                            'user_id' => $user->id,
                            'type' => 'sales',
                            'category' => 'product_sales',
                            'amount' => $data['amount'] ?? 0,
                            'net_amount' => $data['amount'] ?? 0,
                            'currency' => 'SAR',
                            'description' => $data['product'] ?? 'Imported Sale',
                            'revenue_date' => $data['date'] ?? now()->toDateString(),
                            'status' => 'verified',
                            'payment_status' => 'completed',
                            'verified_by' => $user->id,
                            'verified_at' => now(),
                        ]);

                        $importedCount++;
                    } catch (\Exception $e) {
                        $errors[] = 'Row error: '.$e->getMessage();
                    }
                }
            } elseif ($category === 'expenses') {
                // Import expenses data
                $csvData = array_map('str_getcsv', file($file->getPathname()));
                $headers = array_shift($csvData);

                foreach ($csvData as $row) {
                    try {
                        $data = array_combine($headers, $row);

                        Transaction::create([
                            'franchise_id' => $franchise->id,
                            'user_id' => $user->id,
                            'type' => 'expense',
                            'category' => $data['category'] ?? 'Imported Expense',
                            'amount' => $data['amount'] ?? 0,
                            'currency' => 'SAR',
                            'description' => $data['description'] ?? 'Imported Expense',
                            'transaction_date' => $data['date'] ?? now()->toDateString(),
                            'status' => 'completed',
                        ]);

                        $importedCount++;
                    } catch (\Exception $e) {
                        $errors[] = 'Row error: '.$e->getMessage();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} records",
                'imported_count' => $importedCount,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export financial data to file
     */
    public function export(Request $request)
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user',
                ], 404);
            }

            $validated = $request->validate([
                'category' => 'required|in:sales,expenses,profit',
                'period' => 'required|in:daily,monthly,yearly',
                'unit_id' => 'nullable|exists:units,id',
                'year' => 'nullable|integer|min:2020|max:2030',
                'month' => 'nullable|integer|min:1|max:12',
            ]);

            $category = $validated['category'];
            $period = $validated['period'];
            $unitId = $validated['unit_id'] ?? null;
            $year = $validated['year'] ?? Carbon::now()->year;
            $month = $validated['month'] ?? Carbon::now()->month;

            // Get date range
            $dateRange = $this->getDateRange($period, $year, $month);

            // Get data based on category
            $data = [];
            $filename = '';

            if ($category === 'sales') {
                $query = Revenue::where('franchise_id', $franchise->id)
                    ->where('type', 'sales')
                    ->where('status', 'verified')
                    ->whereBetween('revenue_date', [$dateRange['start'], $dateRange['end']]);

                if ($unitId) {
                    $query->where('unit_id', $unitId);
                }

                $sales = $query->get();
                $filename = "sales_{$period}_{$year}.csv";

                $data[] = ['ID', 'Product', 'Amount', 'Date', 'Status'];
                foreach ($sales as $sale) {
                    $data[] = [
                        $sale->id,
                        $sale->description,
                        $sale->net_amount,
                        $sale->revenue_date->format('Y-m-d'),
                        $sale->status,
                    ];
                }
            } elseif ($category === 'expenses') {
                $query = Transaction::where('franchise_id', $franchise->id)
                    ->where('type', 'expense')
                    ->where('status', 'completed')
                    ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']]);

                if ($unitId) {
                    $query->where('unit_id', $unitId);
                }

                $expenses = $query->get();
                $filename = "expenses_{$period}_{$year}.csv";

                $data[] = ['ID', 'Category', 'Amount', 'Date', 'Description'];
                foreach ($expenses as $expense) {
                    $data[] = [
                        $expense->id,
                        $expense->category,
                        $expense->amount,
                        $expense->transaction_date->format('Y-m-d'),
                        $expense->description,
                    ];
                }
            } elseif ($category === 'profit') {
                // Get profit data (simplified for example)
                $filename = "profit_{$period}_{$year}.csv";
                $data[] = ['Date', 'Sales', 'Expenses', 'Profit'];

                // This would need to be implemented based on your profit calculation logic
                $data[] = [
                    $year,
                    $this->getTotalRevenue($franchise->id, $dateRange['start'], $dateRange['end'], $unitId),
                    $this->getTotalExpenses($franchise->id, $dateRange['start'], $dateRange['end'], $unitId),
                    $this->getTotalRevenue($franchise->id, $dateRange['start'], $dateRange['end'], $unitId) -
                        $this->getTotalExpenses($franchise->id, $dateRange['start'], $dateRange['end'], $unitId),
                ];
            }

            // Generate CSV
            $csv = '';
            foreach ($data as $row) {
                $csv .= implode(',', $row)."\n";
            }

            // Return file download response
            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
