<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FinancialController;
use App\Http\Controllers\Api\FranchiseController;
use App\Http\Controllers\Api\FranchiseeDashboardController;
use App\Http\Controllers\Api\FranchisorController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RevenueController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RoyaltyController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TechnicalRequestController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UnitPerformanceController;
use App\Http\Controllers\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::prefix('v1')->group(function () {
    // Other public routes can go here
});

// Protected routes (authentication required)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Onboarding routes (for franchisees)
    Route::prefix('v1/onboarding')->group(function () {
        Route::get('/status', [OnboardingController::class, 'checkOnboardingStatus']);
        Route::post('/complete', [OnboardingController::class, 'completeProfile']);
    });
});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Notification Management Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/{id}/unread', [NotificationController::class, 'markAsUnread']);
        Route::patch('/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead']);
        Route::patch('/mark-multiple-unread', [NotificationController::class, 'markMultipleAsUnread']);
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Franchise Management Routes
    Route::apiResource('franchises', FranchiseController::class);
    Route::prefix('franchises')->group(function () {
        Route::get('statistics', [FranchiseController::class, 'statistics']);
        Route::patch('{franchise}/activate', [FranchiseController::class, 'activate']);
        Route::patch('{franchise}/deactivate', [FranchiseController::class, 'deactivate']);

        // Nested franchise documents
        Route::prefix('{franchise_id}/documents')->group(function () {
            Route::get('/', [DocumentController::class, 'index']);
            Route::post('/', [DocumentController::class, 'store']);
            Route::get('{document_id}', [DocumentController::class, 'show']);
            Route::put('{document_id}', [DocumentController::class, 'update']);
            Route::delete('{document_id}', [DocumentController::class, 'destroy']);
            Route::get('{document_id}/download', [DocumentController::class, 'download']);
            Route::patch('{document_id}/approve', [DocumentController::class, 'approve']);
            Route::patch('{document_id}/reject', [DocumentController::class, 'reject']);
        });

        // Nested franchise products
        Route::prefix('{franchise_id}/products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('{product_id}', [ProductController::class, 'show']);
            Route::put('{product_id}', [ProductController::class, 'update']);
            Route::delete('{product_id}', [ProductController::class, 'destroy']);
        });
    });

    // Lead Management Routes
    Route::apiResource('leads', LeadController::class);
    Route::prefix('leads')->group(function () {
        Route::get('statistics', [LeadController::class, 'statistics']);
        Route::patch('{lead}/convert', [LeadController::class, 'convert']);
        Route::patch('{lead}/mark-lost', [LeadController::class, 'markAsLost']);
        Route::patch('{lead}/assign', [LeadController::class, 'assign']);
        Route::post('{lead}/notes', [LeadController::class, 'addNote']);
    });

    // Note Management Routes
    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'index']); // Get notes for a lead
        Route::post('/', [NoteController::class, 'store']); // Create new note
        Route::get('{note}', [NoteController::class, 'show']); // Get specific note
        Route::put('{note}', [NoteController::class, 'update']); // Update note
        Route::delete('{note}', [NoteController::class, 'destroy']); // Delete note
        Route::delete('{note}/attachments', [NoteController::class, 'removeAttachment']); // Remove attachment
    });

    // Unit Management Routes
    Route::apiResource('units', UnitController::class);
    Route::prefix('units')->group(function () {
        Route::get('statistics', [UnitController::class, 'statistics']);
        Route::patch('{unit}/activate', [UnitController::class, 'activate']);
        Route::patch('{unit}/deactivate', [UnitController::class, 'deactivate']);
        Route::patch('{unit}/close', [UnitController::class, 'close']);

        // Inventory routes for a unit
        Route::prefix('{unit}/inventory')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\UnitInventoryController::class, 'index']);
            Route::post('{product}', [\App\Http\Controllers\Api\UnitInventoryController::class, 'store']);
            Route::put('{product}', [\App\Http\Controllers\Api\UnitInventoryController::class, 'update']);
            Route::delete('{product}', [\App\Http\Controllers\Api\UnitInventoryController::class, 'destroy']);
        });
    });

    // Task Management Routes
    Route::apiResource('tasks', TaskController::class);
    Route::prefix('tasks')->group(function () {
        Route::get('statistics', [TaskController::class, 'statistics']);
        Route::patch('{task}/complete', [TaskController::class, 'complete']);
        Route::patch('{task}/start', [TaskController::class, 'start']);
        Route::patch('{task}/assign', [TaskController::class, 'assign']);
        Route::patch('{task}/progress', [TaskController::class, 'updateProgress']);
    });

    // Admin Technical Request Routes (must be before general resource routes)
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('technical-requests', [AdminController::class, 'technicalRequests']);
        Route::patch('technical-requests/{id}/status', [AdminController::class, 'updateTechnicalRequestStatus']);
        Route::delete('technical-requests/{id}', [AdminController::class, 'deleteTechnicalRequest']);
    });

    // Technical Request Routes (specific routes before resource routes)
    Route::prefix('technical-requests')->group(function () {
        Route::get('statistics', [TechnicalRequestController::class, 'statistics']);
        Route::post('bulk-delete', [TechnicalRequestController::class, 'bulkDelete']);
    });
    Route::apiResource('technical-requests', TechnicalRequestController::class);
    Route::prefix('technical-requests')->group(function () {
        Route::patch('{technicalRequest}/assign', [TechnicalRequestController::class, 'assign']);
        Route::post('{technicalRequest}/respond', [TechnicalRequestController::class, 'respond']);
        Route::patch('{technicalRequest}/resolve', [TechnicalRequestController::class, 'resolve']);
        Route::patch('{technicalRequest}/close', [TechnicalRequestController::class, 'close']);
        Route::patch('{technicalRequest}/escalate', [TechnicalRequestController::class, 'escalate']);
        Route::post('{technicalRequest}/attachments', [TechnicalRequestController::class, 'addAttachment']);
    });

    // Transaction Management Routes
    Route::apiResource('transactions', TransactionController::class);
    Route::prefix('transactions')->group(function () {
        Route::get('statistics', [TransactionController::class, 'statistics']);
        Route::get('revenue', [TransactionController::class, 'revenue']);
        Route::get('expenses', [TransactionController::class, 'expenses']);
        Route::patch('{transaction}/complete', [TransactionController::class, 'complete']);
        Route::patch('{transaction}/cancel', [TransactionController::class, 'cancel']);
        Route::post('{transaction}/refund', [TransactionController::class, 'refund']);
        Route::post('{transaction}/attachments', [TransactionController::class, 'addAttachment']);
    });

    // Royalty Management Routes
    Route::apiResource('royalties', RoyaltyController::class);
    Route::prefix('royalties')->group(function () {
        Route::get('statistics', [RoyaltyController::class, 'statistics']);
        Route::get('pending', [RoyaltyController::class, 'pending']);
        Route::get('overdue', [RoyaltyController::class, 'overdue']);
        Route::patch('{royalty}/mark-paid', [RoyaltyController::class, 'markAsPaid']);
        Route::post('{royalty}/late-fee', [RoyaltyController::class, 'calculateLateFee']);
        Route::post('{royalty}/adjustments', [RoyaltyController::class, 'addAdjustment']);
        Route::post('{royalty}/attachments', [RoyaltyController::class, 'addAttachment']);
        Route::post('generate-monthly', [RoyaltyController::class, 'generateMonthlyRoyalties']);
    });

    // Revenue Management Routes
    Route::apiResource('revenues', RevenueController::class);
    Route::prefix('revenues')->group(function () {
        Route::get('statistics', [RevenueController::class, 'statistics']);
        Route::get('sales', [RevenueController::class, 'sales']);
        Route::get('fees', [RevenueController::class, 'fees']);
        Route::get('breakdown', [RevenueController::class, 'breakdown']);
        Route::get('total-by-period', [RevenueController::class, 'totalByPeriod']);
        Route::patch('{revenue}/verify', [RevenueController::class, 'verify']);
        Route::post('{revenue}/dispute', [RevenueController::class, 'dispute']);
        Route::post('{revenue}/refund', [RevenueController::class, 'refund']);
        Route::post('{revenue}/line-items', [RevenueController::class, 'addLineItem']);
        Route::post('{revenue}/attachments', [RevenueController::class, 'addAttachment']);
    });

    // Document Management Routes
    Route::apiResource('documents', DocumentController::class);
    Route::prefix('documents')->group(function () {
        Route::get('{document}/download', [DocumentController::class, 'download']);
    });

    // Product Management Routes
    Route::apiResource('products', ProductController::class);
    Route::prefix('products')->group(function () {
        Route::get('categories', [ProductController::class, 'categories']);
        Route::patch('{product}/stock', [ProductController::class, 'updateStock']);
    });

    // Review Management Routes
    Route::apiResource('reviews', ReviewController::class);
    Route::prefix('reviews')->group(function () {
        Route::get('statistics', [ReviewController::class, 'statistics']);
        Route::patch('{review}/publish', [ReviewController::class, 'publish']);
        Route::patch('{review}/archive', [ReviewController::class, 'archive']);
        Route::patch('{review}/notes', [ReviewController::class, 'updateNotes']);
    });

    // Unit-specific review routes
    Route::prefix('units/{unit_id}/reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::post('/', [ReviewController::class, 'store']);
        Route::get('statistics', [ReviewController::class, 'statistics']);
    });
});

// Admin-only routes (requires admin role)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1/admin')->group(function () {

    // Admin franchise management
    Route::prefix('franchises')->group(function () {
        Route::get('all-statistics', [FranchiseController::class, 'statistics']);
        Route::post('bulk-activate', [FranchiseController::class, 'bulkActivate']);
        Route::post('bulk-deactivate', [FranchiseController::class, 'bulkDeactivate']);
    });

    // Admin lead management
    Route::prefix('leads')->group(function () {
        Route::get('all-statistics', [LeadController::class, 'statistics']);
        Route::post('bulk-assign', [LeadController::class, 'bulkAssign']);
        Route::post('bulk-convert', [LeadController::class, 'bulkConvert']);
    });

    // Admin unit management
    Route::prefix('units')->group(function () {
        Route::get('all-statistics', [UnitController::class, 'statistics']);
        Route::post('bulk-activate', [UnitController::class, 'bulkActivate']);
        Route::post('bulk-deactivate', [UnitController::class, 'bulkDeactivate']);
    });

    // Admin task management
    Route::prefix('tasks')->group(function () {
        Route::get('all-statistics', [TaskController::class, 'statistics']);
        Route::post('bulk-assign', [TaskController::class, 'bulkAssign']);
        Route::post('bulk-complete', [TaskController::class, 'bulkComplete']);
    });

    // Admin technical request management
    Route::prefix('technical-requests')->group(function () {
        Route::get('all-statistics', [TechnicalRequestController::class, 'statistics']);
        Route::post('bulk-assign', [TechnicalRequestController::class, 'bulkAssign']);
        Route::post('bulk-resolve', [TechnicalRequestController::class, 'bulkResolve']);
    });

    // Admin transaction management
    Route::prefix('transactions')->group(function () {
        Route::get('all-statistics', [TransactionController::class, 'statistics']);
        Route::post('bulk-complete', [TransactionController::class, 'bulkComplete']);
        Route::post('bulk-cancel', [TransactionController::class, 'bulkCancel']);
    });

    // Admin royalty management
    Route::prefix('royalties')->group(function () {
        Route::get('all-statistics', [RoyaltyController::class, 'statistics']);
        Route::post('bulk-mark-paid', [RoyaltyController::class, 'bulkMarkAsPaid']);
        Route::post('generate-all-monthly', [RoyaltyController::class, 'generateAllMonthlyRoyalties']);
    });

    // Admin revenue management
    Route::prefix('revenues')->group(function () {
        Route::get('all-statistics', [RevenueController::class, 'statistics']);
        Route::post('bulk-verify', [RevenueController::class, 'bulkVerify']);
        Route::get('comprehensive-breakdown', [RevenueController::class, 'comprehensiveBreakdown']);
    });
});

// Franchise Owner routes (requires franchise_owner role)
// Route::middleware(['auth:sanctum', 'role:franchisor'])->prefix('v1/franchisor')->group(function () {

//     // Franchise owner can only access their own franchise data
//     Route::get('franchise', [FranchiseController::class, 'myFranchise']);
//     Route::get('units', [UnitController::class, 'myUnits']);
//     Route::get('leads', [LeadController::class, 'myLeads']);
//     Route::get('tasks', [TaskController::class, 'myTasks']);
//     Route::get('transactions', [TransactionController::class, 'myTransactions']);
//     Route::get('royalties', [RoyaltyController::class, 'myRoyalties']);
//     Route::get('revenues', [RevenueController::class, 'myRevenues']);
//     Route::get('technical-requests', [TechnicalRequestController::class, 'myRequests']);

//     // Statistics for franchise owner
//     Route::get('statistics', [FranchiseController::class, 'myStatistics']);
// });

// Franchisor routes (requires franchisor role)
Route::middleware(['auth:sanctum', 'role:franchisor'])->prefix('v1/franchisor')->group(function () {

    // Dashboard endpoints
    Route::get('dashboard/stats', [FranchisorController::class, 'dashboardStats']);
    Route::get('dashboard/finance', [FranchisorController::class, 'financeStats']);
    Route::get('dashboard/leads', [FranchisorController::class, 'leadsStats']);
    Route::get('dashboard/operations', [FranchisorController::class, 'operationsStats']);
    Route::get('dashboard/timeline', [FranchisorController::class, 'timelineStats']);

    // Profile completion status
    Route::get('profile/completion-status', [FranchisorController::class, 'profileCompletionStatus']);

    // Franchise registration and management
    Route::post('franchise/register', [FranchisorController::class, 'registerFranchise']);
    Route::get('franchise/data', [FranchisorController::class, 'getFranchiseData']);
    Route::put('franchise/update', [FranchisorController::class, 'updateFranchise']);
    Route::post('franchise/upload-logo', [FranchisorController::class, 'uploadLogo']);

    // Franchisor can access their franchise network data
    Route::get('franchise', [FranchisorController::class, 'myFranchise']);
    Route::get('franchisees', [FranchisorController::class, 'myFranchisees']);
    Route::get('units', [FranchisorController::class, 'myUnits']);
    Route::get('units/statistics', [FranchisorController::class, 'unitsStatistics']);

    // Lead management for franchisor
    Route::get('leads', [LeadController::class, 'myLeads']);
    Route::get('leads/statistics', [LeadController::class, 'statistics']);
    Route::get('leads/{lead}', [LeadController::class, 'show']);
    Route::post('leads', [LeadController::class, 'store']);
    Route::put('leads/{lead}', [LeadController::class, 'update']);
    Route::delete('leads/{lead}', [LeadController::class, 'destroy']);
    Route::patch('leads/{lead}/assign', [LeadController::class, 'assign']);
    Route::patch('leads/{lead}/convert', [LeadController::class, 'convert']);
    Route::patch('leads/{lead}/mark-lost', [LeadController::class, 'markAsLost']);
    Route::post('leads/{lead}/notes', [LeadController::class, 'addNote']);

    // Task management for franchisor
    Route::get('tasks', [TaskController::class, 'myTasks']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
    Route::patch('tasks/{task}/assign', [TaskController::class, 'assign']);
    Route::patch('tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::patch('tasks/{task}/start', [TaskController::class, 'start']);
    Route::patch('tasks/{task}/progress', [TaskController::class, 'updateProgress']);

    // Technical requests for franchisor network
    Route::get('technical-requests', [TechnicalRequestController::class, 'myRequests']);
    Route::patch('technical-requests/{technicalRequest}/assign', [TechnicalRequestController::class, 'assign']);
    Route::post('technical-requests/{technicalRequest}/respond', [TechnicalRequestController::class, 'respond']);
    Route::patch('technical-requests/{technicalRequest}/resolve', [TechnicalRequestController::class, 'resolve']);
    Route::patch('technical-requests/{technicalRequest}/close', [TechnicalRequestController::class, 'close']);
    Route::patch('technical-requests/{technicalRequest}/escalate', [TechnicalRequestController::class, 'escalate']);

    // Franchisee with unit creation for franchisor
    Route::post('franchisees-with-unit', [FranchisorController::class, 'createFranchiseeWithUnit']);

    // Sales associates management for franchisor
    Route::get('sales-associates', [FranchisorController::class, 'salesAssociatesIndex']);
    Route::post('sales-associates', [FranchisorController::class, 'salesAssociatesStore']);
    Route::get('sales-associates/{id}', [FranchisorController::class, 'salesAssociatesShow']);
    Route::put('sales-associates/{id}', [FranchisorController::class, 'salesAssociatesUpdate']);
    Route::delete('sales-associates/{id}', [FranchisorController::class, 'salesAssociatesDestroy']);

    // Financial data for franchisor
    Route::get('transactions', [TransactionController::class, 'myTransactions']);
    Route::get('royalties', [RoyaltyController::class, 'myRoyalties']);
    Route::get('royalties/statistics', [RoyaltyController::class, 'statistics']);
    Route::get('royalties/export', [RoyaltyController::class, 'export']);
    Route::get('revenues', [RevenueController::class, 'myRevenues']);
    Route::patch('royalties/{royalty}/mark-paid', [RoyaltyController::class, 'markAsPaid']);
    Route::post('royalties/{royalty}/late-fee', [RoyaltyController::class, 'calculateLateFee']);
    Route::post('royalties/{royalty}/adjustments', [RoyaltyController::class, 'addAdjustment']);
    Route::patch('revenues/{revenue}/verify', [RevenueController::class, 'verify']);
    Route::post('revenues/{revenue}/dispute', [RevenueController::class, 'dispute']);

    // Financial overview endpoints
    Route::get('financial/charts', [FinancialController::class, 'charts']);
    Route::get('financial/statistics', [FinancialController::class, 'statistics']);
    Route::get('financial/sales', [FinancialController::class, 'sales']);
    Route::post('financial/sales', [FinancialController::class, 'storeSale']);
    Route::put('financial/sales/{id}', [FinancialController::class, 'updateSale']);
    Route::delete('financial/sales/{id}', [FinancialController::class, 'deleteSale']);
    Route::get('financial/expenses', [FinancialController::class, 'expenses']);
    Route::post('financial/expenses', [FinancialController::class, 'storeExpense']);
    Route::put('financial/expenses/{id}', [FinancialController::class, 'updateExpense']);
    Route::delete('financial/expenses/{id}', [FinancialController::class, 'deleteExpense']);
    Route::get('financial/profit', [FinancialController::class, 'profit']);
    Route::get('financial/unit-performance', [FinancialController::class, 'unitPerformance']);
    Route::post('financial/import', [FinancialController::class, 'import']);
    Route::get('financial/export', [FinancialController::class, 'export']);

    // Performance management for franchisor units
    Route::get('performance/chart-data', [UnitPerformanceController::class, 'chartData']);
    Route::get('performance/top-performers', [UnitPerformanceController::class, 'topPerformers']);
    Route::get('performance/customer-satisfaction', [UnitPerformanceController::class, 'customerSatisfaction']);
    Route::get('performance/ratings', [UnitPerformanceController::class, 'ratings']);
    Route::get('performance/export', [UnitPerformanceController::class, 'export']);
    Route::get('performance/units', [UnitPerformanceController::class, 'units']);
});

// Unit Manager routes (requires franchisee role)
Route::middleware(['auth:sanctum', 'role:franchisee'])->prefix('v1/unit-manager')->group(function () {

    // Unit manager can only access their unit data
    Route::get('unit', [UnitController::class, 'myUnit']);
    Route::get('tasks', [TaskController::class, 'myUnitTasks']);
    Route::get('my-tasks', [FranchiseeDashboardController::class, 'myTasks']);
    Route::patch('my-tasks/{taskId}/status', [FranchiseeDashboardController::class, 'updateMyTaskStatus']);
    Route::get('transactions', [TransactionController::class, 'myUnitTransactions']);
    Route::get('revenues', [RevenueController::class, 'myUnitRevenues']);
    Route::get('technical-requests', [TechnicalRequestController::class, 'myUnitRequests']);
    Route::get('royalties', [RoyaltyController::class, 'myUnitRoyalties']);
    Route::get('royalties/statistics', [RoyaltyController::class, 'myUnitRoyaltyStatistics']);
    Route::get('royalties/export', [RoyaltyController::class, 'exportMyUnitRoyalties']);
    Route::patch('royalties/{royalty}/mark-paid', [RoyaltyController::class, 'markAsPaid']);

    // Statistics for unit manager
    Route::get('statistics', [UnitController::class, 'myUnitStatistics']);

    // Franchisee Dashboard routes
    Route::prefix('dashboard')->group(function () {
        // Performance Management
        Route::get('performance-management', [FranchiseeDashboardController::class, 'performanceManagement']);

        // Sales dashboard endpoints
        Route::get('sales-statistics', [FranchiseeDashboardController::class, 'salesStatistics']);
        Route::get('product-sales', [FranchiseeDashboardController::class, 'productSales']);
        Route::get('monthly-performance', [FranchiseeDashboardController::class, 'monthlyPerformance']);

        // Finance dashboard endpoints
        Route::get('finance-statistics', [FranchiseeDashboardController::class, 'financeStatistics']);
        Route::get('financial-summary', [FranchiseeDashboardController::class, 'financialSummary']);

        // Financial Overview endpoints
        Route::get('financial-overview', [FranchiseeDashboardController::class, 'financialOverview']);
        Route::post('financial-data', [FranchiseeDashboardController::class, 'storeFinancialData']);
        Route::put('financial-data/{id}', [FranchiseeDashboardController::class, 'updateFinancialData']);
        Route::delete('financial-data', [FranchiseeDashboardController::class, 'deleteFinancialData']);

        // Operations dashboard endpoints
        Route::get('store-data', [FranchiseeDashboardController::class, 'storeData']);
        Route::get('staff-data', [FranchiseeDashboardController::class, 'staffData']);
        Route::get('low-stock-chart', [FranchiseeDashboardController::class, 'lowStockChart']);
        Route::get('shift-coverage-chart', [FranchiseeDashboardController::class, 'shiftCoverageChart']);
        Route::get('operations-data', [FranchiseeDashboardController::class, 'operationsData']);
    });

    // Unit Operations routes
    Route::prefix('units')->group(function () {
        // Unit details and overview
        Route::get('details/{unitId?}', [FranchiseeDashboardController::class, 'unitDetails']);
        Route::put('details/{unitId}', [FranchiseeDashboardController::class, 'updateUnitDetails']);

        // Unit operational data - READ operations
        Route::get('tasks/{unitId?}', [FranchiseeDashboardController::class, 'unitTasks']);
        Route::get('staff/{unitId?}', [FranchiseeDashboardController::class, 'unitStaff']);
        Route::get('products/{unitId?}', [FranchiseeDashboardController::class, 'unitProducts']);
        Route::get('reviews/{unitId?}', [FranchiseeDashboardController::class, 'unitReviews']);
        Route::get('documents/{unitId?}', [FranchiseeDashboardController::class, 'unitDocuments']);

        // Unit operational data - CRUD operations
        // Task operations
        Route::post('tasks/{unitId}', [FranchiseeDashboardController::class, 'createTask']);
        Route::put('tasks/{unitId}/{taskId}', [FranchiseeDashboardController::class, 'updateTask']);
        Route::delete('tasks/{unitId}/{taskId}', [FranchiseeDashboardController::class, 'deleteTask']);

        // Staff operations
        Route::post('staff/{unitId}', [FranchiseeDashboardController::class, 'createStaff']);
        Route::put('staff/{unitId}/{staffId}', [FranchiseeDashboardController::class, 'updateStaff']);
        Route::delete('staff/{unitId}/{staffId}', [FranchiseeDashboardController::class, 'deleteStaff']);

        // Inventory management operations (many-to-many product-unit relationship)
        Route::get('available-products/{unitId}', [FranchiseeDashboardController::class, 'getAvailableFranchiseProducts']);
        Route::post('inventory/{unitId}', [FranchiseeDashboardController::class, 'addProductToInventory']);
        Route::put('inventory/{unitId}/{productId}', [FranchiseeDashboardController::class, 'updateInventoryStock']);
        Route::delete('inventory/{unitId}/{productId}', [FranchiseeDashboardController::class, 'removeProductFromInventory']);

        // Legacy product operations (for backward compatibility)
        Route::put('products/{unitId}/{productId}', [FranchiseeDashboardController::class, 'updateProduct']);
        Route::delete('products/{unitId}/{productId}', [FranchiseeDashboardController::class, 'deleteProduct']);

        // Review operations
        Route::post('reviews/{unitId}', [FranchiseeDashboardController::class, 'createReview']);
        Route::put('reviews/{unitId}/{reviewId}', [FranchiseeDashboardController::class, 'updateReview']);
        Route::delete('reviews/{unitId}/{reviewId}', [FranchiseeDashboardController::class, 'deleteReview']);

        // Document operations
        Route::post('documents/{unitId}', [FranchiseeDashboardController::class, 'createDocument']);
        Route::put('documents/{unitId}/{documentId}', [FranchiseeDashboardController::class, 'updateDocument']);
        Route::delete('documents/{unitId}/{documentId}', [FranchiseeDashboardController::class, 'deleteDocument']);
        Route::get('documents/{unitId}/{documentId}/download', [FranchiseeDashboardController::class, 'downloadDocument']);
    });
});

// Employee routes (requires sales role)
Route::middleware(['auth:sanctum', 'role:sales'])->prefix('v1/employee')->group(function () {

    // Employee can access assigned tasks and requests
    Route::get('tasks', [TaskController::class, 'myAssignedTasks']);
    Route::get('technical-requests', [TechnicalRequestController::class, 'myAssignedRequests']);

    // Employee can update their assigned tasks
    Route::patch('tasks/{task}/progress', [TaskController::class, 'updateProgress']);
    Route::patch('tasks/{task}/complete', [TaskController::class, 'complete']);

    // Employee can respond to technical requests
    Route::post('technical-requests/{technicalRequest}/respond', [TechnicalRequestController::class, 'respond']);
    Route::patch('technical-requests/{technicalRequest}/resolve', [TechnicalRequestController::class, 'resolve']);
});

// Admin routes (requires admin role)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1/admin')->group(function () {

    // Dashboard endpoints
    Route::get('dashboard/stats', [AdminController::class, 'dashboardStats']);
    Route::get('dashboard/recent-users', [AdminController::class, 'recentUsers']);
    Route::get('dashboard/chart-data', [AdminController::class, 'chartData']);

    // User management endpoints
    Route::get('users/franchisors', [AdminController::class, 'franchisors']);
    Route::get('users/franchisees', [AdminController::class, 'franchisees']);
    Route::get('users/sales', [AdminController::class, 'salesUsers']);

    // User stats endpoints
    Route::get('users/franchisors/stats', [AdminController::class, 'franchisorStats']);
    Route::get('users/franchisees/stats', [AdminController::class, 'franchiseeStats']);
    Route::get('users/sales/stats', [AdminController::class, 'salesStats']);

    // User CRUD operations
    Route::post('users', [AdminController::class, 'createUser']);
    Route::put('users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('users/{id}', [AdminController::class, 'deleteUser']);
    Route::post('users/{id}/reset-password', [AdminController::class, 'resetPassword']);

    // Franchisee with unit creation
    Route::post('franchisees-with-unit', [AdminController::class, 'createFranchiseeWithUnit']);

    // Technical requests management routes moved to earlier in file to avoid conflicts
});
