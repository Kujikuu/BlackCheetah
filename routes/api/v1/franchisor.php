<?php

use App\Http\Controllers\Api\V1\Franchisor\{FranchisorController, DashboardController, FinancialController as FranchisorFinancialController, LeadManagementController};
use App\Http\Controllers\Api\V1\Resources\{TaskController, TechnicalRequestController, TransactionController, RoyaltyController, RevenueController, LeadController, UnitController, FinancialController, UnitPerformanceController};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Franchisor Routes
|--------------------------------------------------------------------------
|
| Here are the franchisor-specific routes for API version 1.
| These routes require franchisor role authentication.
|
*/

Route::middleware(['auth:sanctum', 'role:franchisor'])->prefix('franchisor')->group(function () {
    // Dashboard endpoints
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'stats'])->name('api.v1.franchisor.dashboard.stats');
        Route::get('finance', [FranchisorFinancialController::class, 'stats'])->name('api.v1.franchisor.dashboard.finance');
        Route::get('leads', [LeadManagementController::class, 'leadsStats'])->name('api.v1.franchisor.dashboard.leads');
        Route::get('operations', [DashboardController::class, 'operationsStats'])->name('api.v1.franchisor.dashboard.operations');
        Route::get('timeline', [DashboardController::class, 'timelineStats'])->name('api.v1.franchisor.dashboard.timeline');
    });

    // Profile completion status
    Route::get('profile/completion-status', [FranchisorController::class, 'profileCompletionStatus'])->name('api.v1.franchisor.profile.completion-status');

    // Franchise registration and management
    Route::prefix('franchise')->group(function () {
        Route::post('register', [FranchisorController::class, 'registerFranchise'])->name('api.v1.franchisor.franchise.register');
        Route::get('data', [FranchisorController::class, 'getFranchiseData'])->name('api.v1.franchisor.franchise.data');
        Route::put('update', [FranchisorController::class, 'updateFranchise'])->name('api.v1.franchisor.franchise.update');
        Route::post('upload-logo', [FranchisorController::class, 'uploadLogo'])->name('api.v1.franchisor.franchise.upload-logo');
    });

    // Marketplace management for franchises
    Route::prefix('franchises')->group(function () {
        Route::patch('{franchise}/assign-broker', [FranchisorController::class, 'assignBroker'])
            ->name('api.v1.franchisor.franchises.assign-broker');
        Route::patch('{franchise}/marketplace-toggle', [FranchisorController::class, 'toggleMarketplaceListing'])
            ->name('api.v1.franchisor.franchises.marketplace-toggle');
    });

    // Franchisor can access their franchise network data
    Route::get('franchise', [FranchisorController::class, 'myFranchise'])->name('api.v1.franchisor.franchise');
    Route::get('franchisees', [LeadManagementController::class, 'myFranchisees'])->name('api.v1.franchisor.franchisees');
    Route::get('units', [FranchisorController::class, 'myUnits'])->name('api.v1.franchisor.units');
    Route::get('units/statistics', [FranchisorController::class, 'unitsStatistics'])->name('api.v1.franchisor.units.statistics');
    Route::get('units/{unitId}/staff', [FranchisorController::class, 'getUnitStaff'])->name('api.v1.franchisor.units.staff');

    // Task management for franchisor
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'myTasks'])->name('api.v1.franchisor.tasks.index');
        Route::post('/', [TaskController::class, 'store'])->name('api.v1.franchisor.tasks.store');
        Route::put('{task}', [TaskController::class, 'update'])->name('api.v1.franchisor.tasks.update');
        Route::delete('{task}', [TaskController::class, 'destroy'])->name('api.v1.franchisor.tasks.destroy');
        Route::patch('{task}/assign', [TaskController::class, 'assign'])->name('api.v1.franchisor.tasks.assign');
        Route::patch('{task}/complete', [TaskController::class, 'complete'])->name('api.v1.franchisor.tasks.complete');
        Route::patch('{task}/start', [TaskController::class, 'start'])->name('api.v1.franchisor.tasks.start');
    });

    // Technical requests for franchisor network
    Route::prefix('technical-requests')->group(function () {
        Route::get('/', [TechnicalRequestController::class, 'myRequests'])->name('api.v1.franchisor.technical-requests.index');
        Route::patch('{technicalRequest}/assign', [TechnicalRequestController::class, 'assign'])->name('api.v1.franchisor.technical-requests.assign');
        Route::post('{technicalRequest}/respond', [TechnicalRequestController::class, 'respond'])->name('api.v1.franchisor.technical-requests.respond');
        Route::patch('{technicalRequest}/resolve', [TechnicalRequestController::class, 'resolve'])->name('api.v1.franchisor.technical-requests.resolve');
        Route::patch('{technicalRequest}/close', [TechnicalRequestController::class, 'close'])->name('api.v1.franchisor.technical-requests.close');
        Route::patch('{technicalRequest}/escalate', [TechnicalRequestController::class, 'escalate'])->name('api.v1.franchisor.technical-requests.escalate');
    });

    // Franchisee with unit creation for franchisor
    Route::post('franchisees-with-unit', [FranchisorController::class, 'createFranchiseeWithUnit'])->name('api.v1.franchisor.franchisees-with-unit.create');

    // Franchise staff management
    Route::prefix('franchise/staff')->group(function () {
        Route::get('/', [FranchisorController::class, 'getFranchiseStaff'])->name('api.v1.franchisor.franchise.staff.index');
        Route::post('/', [FranchisorController::class, 'createFranchiseStaff'])->name('api.v1.franchisor.franchise.staff.store');
        Route::put('{staff}', [FranchisorController::class, 'updateFranchiseStaff'])->name('api.v1.franchisor.franchise.staff.update');
        Route::delete('{staff}', [FranchisorController::class, 'deleteFranchiseStaff'])->name('api.v1.franchisor.franchise.staff.destroy');
        Route::get('statistics', [FranchisorController::class, 'getFranchiseStaffStatistics'])->name('api.v1.franchisor.franchise.staff.statistics');
    });

    // Brokers management for franchisor
    Route::prefix('brokers')->group(function () {
        Route::get('/', [FranchisorController::class, 'brokersIndex'])->name('api.v1.franchisor.brokers.index');
        Route::post('/', [FranchisorController::class, 'brokersStore'])->name('api.v1.franchisor.brokers.store');
        Route::get('{id}', [FranchisorController::class, 'brokersShow'])->name('api.v1.franchisor.brokers.show');
        Route::put('{id}', [FranchisorController::class, 'brokersUpdate'])->name('api.v1.franchisor.brokers.update');
        Route::delete('{id}', [FranchisorController::class, 'brokersDestroy'])->name('api.v1.franchisor.brokers.destroy');
    });

    // Financial data for franchisor
    Route::prefix('financial')->group(function () {
        Route::get('transactions', [TransactionController::class, 'myTransactions'])->name('api.v1.franchisor.financial.transactions');
        Route::get('royalties', [RoyaltyController::class, 'myRoyalties'])->name('api.v1.franchisor.financial.royalties');
        Route::post('royalties', [RoyaltyController::class, 'store'])->name('api.v1.franchisor.financial.royalties.store');
        Route::get('royalties/statistics', [RoyaltyController::class, 'statistics'])->name('api.v1.franchisor.financial.royalties.statistics');
        Route::get('royalties/export', [RoyaltyController::class, 'export'])->name('api.v1.franchisor.financial.royalties.export');
        Route::get('revenues', [RevenueController::class, 'myRevenues'])->name('api.v1.franchisor.financial.revenues');
        Route::patch('royalties/{royalty}/mark-paid', [RoyaltyController::class, 'markAsPaid'])->name('api.v1.franchisor.financial.royalties.mark-paid');
        Route::post('royalties/{royalty}/late-fee', [RoyaltyController::class, 'calculateLateFee'])->name('api.v1.franchisor.financial.royalties.late-fee');
        Route::post('royalties/{royalty}/adjustments', [RoyaltyController::class, 'addAdjustment'])->name('api.v1.franchisor.financial.royalties.adjustments');
        Route::patch('revenues/{revenue}/verify', [RevenueController::class, 'verify'])->name('api.v1.franchisor.financial.revenues.verify');
        Route::post('revenues/{revenue}/dispute', [RevenueController::class, 'dispute'])->name('api.v1.franchisor.financial.revenues.dispute');
    });

    // Financial overview endpoints
    Route::prefix('financial')->group(function () {
        Route::get('charts', [FinancialController::class, 'charts'])->name('api.v1.franchisor.financial.charts');
        Route::get('statistics', [FinancialController::class, 'statistics'])->name('api.v1.franchisor.financial.statistics');
        Route::get('sales', [FinancialController::class, 'sales'])->name('api.v1.franchisor.financial.sales');
        Route::post('sales', [FinancialController::class, 'storeSale'])->name('api.v1.franchisor.financial.sales.store');
        Route::put('sales/{id}', [FinancialController::class, 'updateSale'])->name('api.v1.franchisor.financial.sales.update');
        Route::delete('sales/{id}', [FinancialController::class, 'deleteSale'])->name('api.v1.franchisor.financial.sales.destroy');
        Route::get('expenses', [FinancialController::class, 'expenses'])->name('api.v1.franchisor.financial.expenses');
        Route::post('expenses', [FinancialController::class, 'storeExpense'])->name('api.v1.franchisor.financial.expenses.store');
        Route::put('expenses/{id}', [FinancialController::class, 'updateExpense'])->name('api.v1.franchisor.financial.expenses.update');
        Route::delete('expenses/{id}', [FinancialController::class, 'deleteExpense'])->name('api.v1.franchisor.financial.expenses.destroy');
        Route::get('profit', [FinancialController::class, 'profit'])->name('api.v1.franchisor.financial.profit');
        Route::get('unit-performance', [FinancialController::class, 'unitPerformance'])->name('api.v1.franchisor.financial.unit-performance');
        Route::post('import', [FinancialController::class, 'import'])->name('api.v1.franchisor.financial.import');
        Route::get('export', [FinancialController::class, 'export'])->name('api.v1.franchisor.financial.export');
    });

    // Performance management for franchisor units
    Route::prefix('performance')->group(function () {
        Route::get('chart-data', [UnitPerformanceController::class, 'chartData'])->name('api.v1.franchisor.performance.chart-data');
        Route::get('top-performers', [UnitPerformanceController::class, 'topPerformers'])->name('api.v1.franchisor.performance.top-performers');
        Route::get('customer-satisfaction', [UnitPerformanceController::class, 'customerSatisfaction'])->name('api.v1.franchisor.performance.customer-satisfaction');
        Route::get('ratings', [UnitPerformanceController::class, 'ratings'])->name('api.v1.franchisor.performance.ratings');
        Route::get('export', [UnitPerformanceController::class, 'export'])->name('api.v1.franchisor.performance.export');
        Route::get('units', [UnitPerformanceController::class, 'units'])->name('api.v1.franchisor.performance.units');
    });

    // Lead management for franchisor (manage sales team leads)
    // Note: Lead creation is restricted to brokers only. Franchisors can view, update, and manage leads.
    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('api.v1.franchisor.leads.index');
        Route::get('statistics', [LeadController::class, 'statistics'])->name('api.v1.franchisor.leads.statistics');
        Route::get('{lead}', [LeadController::class, 'show'])->name('api.v1.franchisor.leads.show');
        // Route::post('/', [LeadController::class, 'store'])->name('api.v1.franchisor.leads.store'); // Disabled: Only brokers can create leads
        Route::put('{lead}', [LeadController::class, 'update'])->name('api.v1.franchisor.leads.update');
        Route::delete('{lead}', [LeadController::class, 'destroy'])->name('api.v1.franchisor.leads.destroy');
        Route::patch('{lead}/assign', [LeadController::class, 'assign'])->name('api.v1.franchisor.leads.assign');
        Route::patch('{lead}/convert', [LeadController::class, 'convert'])->name('api.v1.franchisor.leads.convert');
        Route::patch('{lead}/mark-lost', [LeadController::class, 'markAsLost'])->name('api.v1.franchisor.leads.mark-lost');
        Route::post('{lead}/notes', [LeadController::class, 'addNote'])->name('api.v1.franchisor.leads.add-note');
        Route::post('import', [LeadController::class, 'importCsv'])->name('api.v1.franchisor.leads.import');
        Route::get('export', [LeadController::class, 'exportCsv'])->name('api.v1.franchisor.leads.export');
        Route::delete('bulk-delete', [LeadController::class, 'bulkDelete'])->name('api.v1.franchisor.leads.bulk-delete');
    });
});
