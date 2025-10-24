<?php

use App\Http\Controllers\Api\V1\Broker\{BrokerController, PropertyController};
use App\Http\Controllers\Api\V1\Resources\{TaskController, TechnicalRequestController, LeadController};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Broker Routes
|--------------------------------------------------------------------------
|
| Here are the broker employee routes for API version 1.
| These routes require broker role authentication.
|
*/

Route::middleware(['auth:sanctum', 'role:broker'])->prefix('broker')->group(function () {
    // Franchise marketplace management for brokers
    Route::get('assigned-franchises', [BrokerController::class, 'getAssignedFranchises'])
        ->name('api.v1.broker.assigned-franchises');
    Route::patch('franchises/{franchise}/marketplace-toggle', [BrokerController::class, 'toggleMarketplaceListing'])
        ->name('api.v1.broker.franchises.marketplace-toggle');

    // Employee routes (basic broker role functionality)
    Route::prefix('employee')->group(function () {
        // Employee can access assigned tasks and requests
        Route::get('tasks', [TaskController::class, 'myAssignedTasks'])->name('api.v1.broker.employee.tasks');
        Route::get('technical-requests', [TechnicalRequestController::class, 'myAssignedRequests'])->name('api.v1.broker.employee.technical-requests');

        // Employee can update their assigned tasks
        Route::patch('tasks/{task}/complete', [TaskController::class, 'complete'])->name('api.v1.broker.employee.tasks.complete');

        // Employee can respond to technical requests
        Route::post('technical-requests/{technicalRequest}/respond', [TechnicalRequestController::class, 'respond'])->name('api.v1.broker.employee.technical-requests.respond');
        Route::patch('technical-requests/{technicalRequest}/resolve', [TechnicalRequestController::class, 'resolve'])->name('api.v1.broker.employee.technical-requests.resolve');
    });

    // Broker routes - Lead Management
    Route::prefix('brokers')->group(function () {
        // Lead management for broker users
        Route::prefix('leads')->group(function () {
            Route::get('/', [LeadController::class, 'myLeads'])->name('api.v1.broker.leads.index');
            Route::get('statistics', [LeadController::class, 'statistics'])->name('api.v1.broker.leads.statistics');
            Route::get('{lead}', [LeadController::class, 'show'])->name('api.v1.broker.leads.show');
            Route::post('/', [LeadController::class, 'store'])->name('api.v1.broker.leads.store');
            Route::put('{lead}', [LeadController::class, 'update'])->name('api.v1.broker.leads.update');
            Route::delete('{lead}', [LeadController::class, 'destroy'])->name('api.v1.broker.leads.destroy');
            Route::patch('{lead}/assign', [LeadController::class, 'assign'])->name('api.v1.broker.leads.assign');
            Route::patch('{lead}/convert', [LeadController::class, 'convert'])->name('api.v1.broker.leads.convert');
            Route::patch('{lead}/mark-lost', [LeadController::class, 'markAsLost'])->name('api.v1.broker.leads.mark-lost');
            Route::post('{lead}/notes', [LeadController::class, 'addNote'])->name('api.v1.broker.leads.add-note');
            Route::post('import', [LeadController::class, 'importCsv'])->name('api.v1.broker.leads.import');
            Route::get('export', [LeadController::class, 'exportCsv'])->name('api.v1.broker.leads.export');
            Route::delete('bulk-delete', [LeadController::class, 'bulkDelete'])->name('api.v1.broker.leads.bulk-delete');
        });

        // Task management for broker users
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'myBrokerTasks'])->name('api.v1.broker.tasks.index');
            Route::get('statistics', [TaskController::class, 'brokerTasksStatistics'])->name('api.v1.broker.tasks.statistics');
            Route::patch('{task}/status', [TaskController::class, 'updateBrokerTaskStatus'])->name('api.v1.broker.tasks.update-status');
        });

        // Property management for brokers
        Route::prefix('properties')->group(function () {
            Route::get('/', [PropertyController::class, 'index'])->name('api.v1.broker.properties.index');
            Route::post('/', [PropertyController::class, 'store'])->name('api.v1.broker.properties.store');
            Route::get('{property}', [PropertyController::class, 'show'])->name('api.v1.broker.properties.show');
            Route::put('{property}', [PropertyController::class, 'update'])->name('api.v1.broker.properties.update');
            Route::delete('{property}', [PropertyController::class, 'destroy'])->name('api.v1.broker.properties.destroy');
            Route::post('bulk-delete', [PropertyController::class, 'bulkDelete'])->name('api.v1.broker.properties.bulk-delete');
            Route::patch('{property}/mark-leased', [PropertyController::class, 'markLeased'])->name('api.v1.broker.properties.mark-leased');
        });
    });
});
