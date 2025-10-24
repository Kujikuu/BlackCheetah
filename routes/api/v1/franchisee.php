<?php

use App\Http\Controllers\Api\V1\Franchisee\{FranchiseeController, DashboardController};
use App\Http\Controllers\Api\V1\Resources\{UnitController, TaskController, TransactionController, RevenueController, TechnicalRequestController, RoyaltyController};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Franchisee Routes
|--------------------------------------------------------------------------
|
| Here are the franchisee/unit-manager routes for API version 1.
| These routes require franchisee role authentication.
|
*/

Route::middleware(['auth:sanctum', 'role:franchisee'])->prefix('unit-manager')->group(function () {
    // Unit manager can only access their unit data
    Route::get('unit', [UnitController::class, 'myUnit'])->name('api.v1.franchisee.unit');
    Route::get('tasks', [TaskController::class, 'myUnitTasks'])->name('api.v1.franchisee.tasks');
    Route::get('my-tasks', [FranchiseeController::class, 'myTasks'])->name('api.v1.franchisee.my-tasks');
    Route::patch('my-tasks/{taskId}/status', [FranchiseeController::class, 'updateMyTaskStatus'])->name('api.v1.franchisee.my-tasks.update-status');
    
    // Task management - franchisee can create tasks for franchisor
    Route::prefix('tasks')->group(function () {
        Route::post('/', [TaskController::class, 'store'])->name('api.v1.franchisee.tasks.store');
        Route::put('{task}', [TaskController::class, 'update'])->name('api.v1.franchisee.tasks.update');
        Route::delete('{task}', [TaskController::class, 'destroy'])->name('api.v1.franchisee.tasks.destroy');
    });
    
    Route::get('transactions', [TransactionController::class, 'myUnitTransactions'])->name('api.v1.franchisee.transactions');
    Route::get('revenues', [RevenueController::class, 'myUnitRevenues'])->name('api.v1.franchisee.revenues');
    Route::get('technical-requests', [TechnicalRequestController::class, 'myUnitRequests'])->name('api.v1.franchisee.technical-requests');
    Route::get('royalties', [RoyaltyController::class, 'myUnitRoyalties'])->name('api.v1.franchisee.royalties');
    Route::get('royalties/statistics', [RoyaltyController::class, 'myUnitRoyaltyStatistics'])->name('api.v1.franchisee.royalties.statistics');
    Route::get('royalties/export', [RoyaltyController::class, 'exportMyUnitRoyalties'])->name('api.v1.franchisee.royalties.export');
    Route::patch('royalties/{royalty}/mark-paid', [RoyaltyController::class, 'markAsPaid'])->name('api.v1.franchisee.royalties.mark-paid');

    // Statistics for unit manager
    Route::get('statistics', [UnitController::class, 'myUnitStatistics'])->name('api.v1.franchisee.statistics');

    // Franchisee Dashboard routes
    Route::prefix('dashboard')->group(function () {
        // Performance Management
        Route::get('performance-management', [FranchiseeController::class, 'performanceManagement'])->name('api.v1.franchisee.dashboard.performance-management');

        // Sales dashboard endpoints
        Route::get('sales-statistics', [DashboardController::class, 'salesStatistics'])->name('api.v1.franchisee.dashboard.sales-statistics');
        Route::get('product-sales', [DashboardController::class, 'productSales'])->name('api.v1.franchisee.dashboard.product-sales');
        Route::get('monthly-performance', [DashboardController::class, 'monthlyPerformance'])->name('api.v1.franchisee.dashboard.monthly-performance');

        // Finance dashboard endpoints
        Route::get('finance-statistics', [DashboardController::class, 'financeStatistics'])->name('api.v1.franchisee.dashboard.finance-statistics');
        Route::get('financial-summary', [DashboardController::class, 'financialSummary'])->name('api.v1.franchisee.dashboard.financial-summary');

        // Financial Overview endpoints
        Route::get('financial-overview', [FranchiseeController::class, 'financialOverview'])->name('api.v1.franchisee.dashboard.financial-overview');
        Route::post('financial-data', [FranchiseeController::class, 'storeFinancialData'])->name('api.v1.franchisee.dashboard.financial-data.store');
        Route::put('financial-data/{id}', [FranchiseeController::class, 'updateFinancialData'])->name('api.v1.franchisee.dashboard.financial-data.update');
        Route::delete('financial-data', [FranchiseeController::class, 'deleteFinancialData'])->name('api.v1.franchisee.dashboard.financial-data.destroy');

        // Operations dashboard endpoints
        Route::get('store-data', [DashboardController::class, 'storeData'])->name('api.v1.franchisee.dashboard.store-data');
        Route::get('staff-data', [DashboardController::class, 'staffData'])->name('api.v1.franchisee.dashboard.staff-data');
        Route::get('low-stock-chart', [DashboardController::class, 'lowStockChart'])->name('api.v1.franchisee.dashboard.low-stock-chart');
        Route::get('shift-coverage-chart', [DashboardController::class, 'shiftCoverageChart'])->name('api.v1.franchisee.dashboard.shift-coverage-chart');
        Route::get('operations-data', [DashboardController::class, 'operationsData'])->name('api.v1.franchisee.dashboard.operations-data');
    });

    // Unit Operations routes
    Route::prefix('units')->group(function () {
        // Unit details and overview
        Route::get('details/{unitId?}', [FranchiseeController::class, 'unitDetails'])->name('api.v1.franchisee.units.details');
        Route::put('details/{unitId}', [FranchiseeController::class, 'updateUnitDetails'])->name('api.v1.franchisee.units.details.update');

        // Unit operational data - READ operations
        Route::get('tasks/{unitId?}', [FranchiseeController::class, 'unitTasks'])->name('api.v1.franchisee.units.tasks');
        Route::get('staff/{unitId?}', [FranchiseeController::class, 'unitStaff'])->name('api.v1.franchisee.units.staff');
        Route::get('products/{unitId?}', [FranchiseeController::class, 'unitProducts'])->name('api.v1.franchisee.units.products');
        Route::get('reviews/{unitId?}', [FranchiseeController::class, 'unitReviews'])->name('api.v1.franchisee.units.reviews');
        Route::get('documents/{unitId?}', [FranchiseeController::class, 'unitDocuments'])->name('api.v1.franchisee.units.documents');

        // Unit operational data - CRUD operations
        // Task operations
        Route::post('tasks/{unitId}', [FranchiseeController::class, 'createTask'])->name('api.v1.franchisee.units.tasks.store');
        Route::put('tasks/{unitId}/{taskId}', [FranchiseeController::class, 'updateTask'])->name('api.v1.franchisee.units.tasks.update');
        Route::delete('tasks/{unitId}/{taskId}', [FranchiseeController::class, 'deleteTask'])->name('api.v1.franchisee.units.tasks.destroy');

        // Staff operations
        Route::post('staff/{unitId}', [FranchiseeController::class, 'createStaff'])->name('api.v1.franchisee.units.staff.store');
        Route::put('staff/{unitId}/{staffId}', [FranchiseeController::class, 'updateStaff'])->name('api.v1.franchisee.units.staff.update');
        Route::delete('staff/{unitId}/{staffId}', [FranchiseeController::class, 'deleteStaff'])->name('api.v1.franchisee.units.staff.destroy');

        // Inventory management operations (many-to-many product-unit relationship)
        Route::get('available-products/{unitId}', [FranchiseeController::class, 'getAvailableFranchiseProducts'])->name('api.v1.franchisee.units.available-products');
        Route::post('inventory/{unitId}', [FranchiseeController::class, 'addProductToInventory'])->name('api.v1.franchisee.units.inventory.store');
        Route::put('inventory/{unitId}/{productId}', [FranchiseeController::class, 'updateInventoryStock'])->name('api.v1.franchisee.units.inventory.update');
        Route::delete('inventory/{unitId}/{productId}', [FranchiseeController::class, 'removeProductFromInventory'])->name('api.v1.franchisee.units.inventory.destroy');

        // Legacy product operations (for backward compatibility)
        Route::put('products/{unitId}/{productId}', [FranchiseeController::class, 'updateProduct'])->name('api.v1.franchisee.units.products.update');
        Route::delete('products/{unitId}/{productId}', [FranchiseeController::class, 'deleteProduct'])->name('api.v1.franchisee.units.products.destroy');

        // Review operations
        Route::post('reviews/{unitId}', [FranchiseeController::class, 'createReview'])->name('api.v1.franchisee.units.reviews.store');
        Route::put('reviews/{unitId}/{reviewId}', [FranchiseeController::class, 'updateReview'])->name('api.v1.franchisee.units.reviews.update');
        Route::delete('reviews/{unitId}/{reviewId}', [FranchiseeController::class, 'deleteReview'])->name('api.v1.franchisee.units.reviews.destroy');

        // Document operations
        Route::post('documents/{unitId}', [FranchiseeController::class, 'createDocument'])->name('api.v1.franchisee.units.documents.store');
        Route::put('documents/{unitId}/{documentId}', [FranchiseeController::class, 'updateDocument'])->name('api.v1.franchisee.units.documents.update');
        Route::delete('documents/{unitId}/{documentId}', [FranchiseeController::class, 'deleteDocument'])->name('api.v1.franchisee.units.documents.destroy');
        Route::get('documents/{unitId}/{documentId}/download', [FranchiseeController::class, 'downloadDocument'])->name('api.v1.franchisee.units.documents.download');
    });
});
