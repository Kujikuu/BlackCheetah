<?php

use App\Http\Controllers\Api\V1\Resources\{TaskController, TechnicalRequestController, LeadController};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Sales Routes
|--------------------------------------------------------------------------
|
| Here are the sales employee routes for API version 1.
| These routes require sales role authentication.
|
*/

Route::middleware(['auth:sanctum', 'role:sales'])->group(function () {
    // Employee routes (basic sales role functionality)
    Route::prefix('employee')->group(function () {
        // Employee can access assigned tasks and requests
        Route::get('tasks', [TaskController::class, 'myAssignedTasks'])->name('api.v1.sales.employee.tasks');
        Route::get('technical-requests', [TechnicalRequestController::class, 'myAssignedRequests'])->name('api.v1.sales.employee.technical-requests');

        // Employee can update their assigned tasks
        Route::patch('tasks/{task}/complete', [TaskController::class, 'complete'])->name('api.v1.sales.employee.tasks.complete');

        // Employee can respond to technical requests
        Route::post('technical-requests/{technicalRequest}/respond', [TechnicalRequestController::class, 'respond'])->name('api.v1.sales.employee.technical-requests.respond');
        Route::patch('technical-requests/{technicalRequest}/resolve', [TechnicalRequestController::class, 'resolve'])->name('api.v1.sales.employee.technical-requests.resolve');
    });

    // Sales routes - Lead Management
    Route::prefix('sales')->group(function () {
        // Lead management for sales users
        Route::prefix('leads')->group(function () {
            Route::get('/', [LeadController::class, 'myLeads'])->name('api.v1.sales.leads.index');
            Route::get('statistics', [LeadController::class, 'statistics'])->name('api.v1.sales.leads.statistics');
            Route::get('{lead}', [LeadController::class, 'show'])->name('api.v1.sales.leads.show');
            Route::post('/', [LeadController::class, 'store'])->name('api.v1.sales.leads.store');
            Route::put('{lead}', [LeadController::class, 'update'])->name('api.v1.sales.leads.update');
            Route::delete('{lead}', [LeadController::class, 'destroy'])->name('api.v1.sales.leads.destroy');
            Route::patch('{lead}/assign', [LeadController::class, 'assign'])->name('api.v1.sales.leads.assign');
            Route::patch('{lead}/convert', [LeadController::class, 'convert'])->name('api.v1.sales.leads.convert');
            Route::patch('{lead}/mark-lost', [LeadController::class, 'markAsLost'])->name('api.v1.sales.leads.mark-lost');
            Route::post('{lead}/notes', [LeadController::class, 'addNote'])->name('api.v1.sales.leads.add-note');
            Route::post('import', [LeadController::class, 'importCsv'])->name('api.v1.sales.leads.import');
            Route::get('export', [LeadController::class, 'exportCsv'])->name('api.v1.sales.leads.export');
            Route::delete('bulk-delete', [LeadController::class, 'bulkDelete'])->name('api.v1.sales.leads.bulk-delete');
        });

        // Task management for sales users
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'mySalesTasks'])->name('api.v1.sales.tasks.index');
            Route::get('statistics', [TaskController::class, 'salesTasksStatistics'])->name('api.v1.sales.tasks.statistics');
            Route::patch('{task}/status', [TaskController::class, 'updateSalesTaskStatus'])->name('api.v1.sales.tasks.update-status');
        });
    });
});
