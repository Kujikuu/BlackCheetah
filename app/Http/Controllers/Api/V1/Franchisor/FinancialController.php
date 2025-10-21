<?php

namespace App\Http\Controllers\Api\V1\Franchisor;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\Royalty;
use App\Models\Transaction;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Franchisor Financial Controller
 * 
 * Handles financial analytics and reporting for franchisors
 */
class FinancialController extends BaseResourceController
{
    /**
     * Get finance dashboard data
     */
    public function stats(): JsonResponse
    {
        try {
            $user = Auth::user();
            $franchise = Franchise::where('franchisor_id', $user->id)->first();

            if (! $franchise) {
                return $this->notFoundResponse('No franchise found for this user');
            }

            // Get last 12 months for charts
            $months = collect();
            for ($i = 11; $i >= 0; $i--) {
                $months->push(Carbon::now()->subMonths($i)->format('Y-m'));
            }

            // Revenue by month
            $revenueByMonth = Revenue::where('franchise_id', $franchise->id)
                ->whereIn(DB::raw('CONCAT(period_year, "-", LPAD(period_month, 2, "0"))'), $months)
                ->groupBy(DB::raw('CONCAT(period_year, "-", LPAD(period_month, 2, "0"))'))
                ->selectRaw('CONCAT(period_year, "-", LPAD(period_month, 2, "0")) as month, SUM(amount) as total')
                ->pluck('total', 'month');

            // Royalties by month
            $royaltiesByMonth = Royalty::where('franchise_id', $franchise->id)
                ->where('status', 'paid')
                ->whereIn(DB::raw('CONCAT(period_year, "-", LPAD(period_month, 2, "0"))'), $months)
                ->groupBy(DB::raw('CONCAT(period_year, "-", LPAD(period_month, 2, "0"))'))
                ->selectRaw('CONCAT(period_year, "-", LPAD(period_month, 2, "0")) as month, SUM(total_amount) as total')
                ->pluck('total', 'month');

            // Expenses by month (from transactions)
            $expensesByMonth = Transaction::where('franchise_id', $franchise->id)
                ->where('type', 'expense')
                ->whereIn(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")'), $months)
                ->groupBy(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")'))
                ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month, SUM(amount) as total')
                ->pluck('total', 'month');

            // Format data for charts
            $chartData = $months->map(function ($month) use ($revenueByMonth, $royaltiesByMonth, $expensesByMonth) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                    'revenue' => $revenueByMonth->get($month, 0),
                    'royalties' => $royaltiesByMonth->get($month, 0),
                    'expenses' => $expensesByMonth->get($month, 0),
                ];
            });

            // Current month totals
            $currentMonth = Carbon::now()->format('Y-m');
            $previousMonth = Carbon::now()->subMonth()->format('Y-m');

            $totalSales = $revenueByMonth->get($currentMonth, 0);
            $previousSales = $revenueByMonth->get($previousMonth, 0);
            $salesChange = $previousSales > 0 ? (($totalSales - $previousSales) / $previousSales) * 100 : 0;

            $totalExpenses = $expensesByMonth->get($currentMonth, 0);
            $previousExpenses = $expensesByMonth->get($previousMonth, 0);
            $expensesChange = $previousExpenses > 0 ? (($totalExpenses - $previousExpenses) / $previousExpenses) * 100 : 0;

            $totalProfit = $totalSales - $totalExpenses;
            $previousProfit = $previousSales - $previousExpenses;
            $profitChange = $previousProfit > 0 ? (($totalProfit - $previousProfit) / $previousProfit) * 100 : 0;

            $profitMargin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;
            $previousMargin = $previousSales > 0 ? ($previousProfit / $previousSales) * 100 : 0;
            $marginChange = $profitMargin - $previousMargin;

            // Top performing units - simplified for now since units might not have direct revenues
            $topUnits = Unit::where('franchise_id', $franchise->id)
                ->limit(5)
                ->get()
                ->map(function ($unit, $index) {
                    return [
                        'name' => $unit->name,
                        'sales' => rand(10000, 50000), // Mock data for now
                        'royalty' => rand(1000, 5000), // Mock data for now
                    ];
                });

            return $this->successResponse([
                'stats' => [
                    'total_sales' => $totalSales,
                    'sales_change' => round($salesChange, 2),
                    'total_expenses' => $totalExpenses,
                    'expenses_change' => round($expensesChange, 2),
                    'net_profit' => $totalProfit,
                    'profit_change' => round($profitChange, 2),
                    'profit_margin' => round($profitMargin, 2),
                    'margin_change' => round($marginChange, 2),
                ],
                'top_stores_sales' => $topUnits->toArray(),
                'top_stores_royalty' => $topUnits->toArray(),
                'sales_chart' => $chartData->map(function ($item) {
                    return [
                        'month' => $item['month'],
                        'amount' => $item['revenue'],
                    ];
                })->toArray(),
                'expenses_chart' => $chartData->map(function ($item) {
                    return [
                        'month' => $item['month'],
                        'amount' => $item['expenses'],
                    ];
                })->toArray(),
                'profit_chart' => $chartData->map(function ($item) {
                    return [
                        'month' => $item['month'],
                        'amount' => $item['revenue'] - $item['expenses'],
                    ];
                })->toArray(),
                'royalty_chart' => $chartData->map(function ($item) {
                    return [
                        'month' => $item['month'],
                        'amount' => $item['royalties'],
                    ];
                })->toArray(),
                'monthly_breakdown' => $chartData->map(function ($item) {
                    return [
                        'month' => $item['month'],
                        'sales' => $item['revenue'],
                        'expenses' => $item['expenses'],
                        'royalties' => $item['royalties'],
                        'profit' => $item['revenue'] - $item['expenses'],
                    ];
                })->toArray(),
            ], 'Finance statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch finance statistics', 500, $e->getMessage());
        }
    }
}
