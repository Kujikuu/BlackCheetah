<?php

use App\Http\Controllers\Api\V1\Admin\{AdminController, DashboardController};
use App\Http\Controllers\Api\V1\Resources\TechnicalRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Admin Routes
|--------------------------------------------------------------------------
|
| Here are the admin-only routes for API version 1.
| These routes require admin role authentication.
|
*/

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Admin technical request management
    Route::prefix('technical-requests')->group(function () {
        Route::get('all-statistics', [TechnicalRequestController::class, 'statistics'])->name('api.v1.admin.technical-requests.all-statistics');
        Route::post('bulk-assign', [TechnicalRequestController::class, 'bulkAssign'])->name('api.v1.admin.technical-requests.bulk-assign');
        Route::post('bulk-resolve', [TechnicalRequestController::class, 'bulkResolve'])->name('api.v1.admin.technical-requests.bulk-resolve');
        Route::get('/', [AdminController::class, 'technicalRequests'])->name('api.v1.admin.technical-requests.index');
        Route::patch('{id}/status', [AdminController::class, 'updateTechnicalRequestStatus'])->name('api.v1.admin.technical-requests.update-status');
        Route::delete('{id}', [AdminController::class, 'deleteTechnicalRequest'])->name('api.v1.admin.technical-requests.destroy');
    });

    // Dashboard endpoints
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'dashboardStats'])->name('api.v1.admin.dashboard.stats');
        Route::get('recent-users', [DashboardController::class, 'recentUsers'])->name('api.v1.admin.dashboard.recent-users');
        Route::get('chart-data', [DashboardController::class, 'chartData'])->name('api.v1.admin.dashboard.chart-data');
    });

    // User management endpoints
    Route::prefix('users')->group(function () {
        Route::get('franchisors', [AdminController::class, 'franchisors'])->name('api.v1.admin.users.franchisors');
        Route::get('franchisees', [AdminController::class, 'franchisees'])->name('api.v1.admin.users.franchisees');
        Route::get('sales', [AdminController::class, 'salesUsers'])->name('api.v1.admin.users.sales');

        // User stats endpoints
        Route::get('franchisors/stats', [AdminController::class, 'franchisorStats'])->name('api.v1.admin.users.franchisors.stats');
        Route::get('franchisees/stats', [AdminController::class, 'franchiseeStats'])->name('api.v1.admin.users.franchisees.stats');
        Route::get('sales/stats', [AdminController::class, 'salesStats'])->name('api.v1.admin.users.sales.stats');

        // User CRUD operations
        Route::post('/', [AdminController::class, 'createUser'])->name('api.v1.admin.users.store');
        Route::put('{id}', [AdminController::class, 'updateUser'])->name('api.v1.admin.users.update');
        Route::delete('{id}', [AdminController::class, 'deleteUser'])->name('api.v1.admin.users.destroy');
        Route::post('{id}/reset-password', [AdminController::class, 'resetPassword'])->name('api.v1.admin.users.reset-password');
    });

    // Franchisee with unit creation
    Route::post('franchisees-with-unit', [AdminController::class, 'createFranchiseeWithUnit'])->name('api.v1.admin.franchisees-with-unit.create');
});
