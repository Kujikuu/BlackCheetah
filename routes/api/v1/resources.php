<?php

use App\Http\Controllers\Api\V1\Resources\{DocumentController, LeadController, NoteController, ProductController, ReviewController, TaskController, TechnicalRequestController, TransactionController, RevenueController, RoyaltyController, UnitController, UnitPerformanceController, FranchiseController, UnitInventoryController};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Business Resources Routes
|--------------------------------------------------------------------------
|
| Here are the business resource routes for API version 1.
| These routes handle core business entities like franchises, leads, tasks, etc.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Franchise Management Routes
    Route::apiResource('franchises', FranchiseController::class);
    Route::prefix('franchises')->group(function () {
        Route::get('statistics', [FranchiseController::class, 'statistics'])->name('api.v1.franchises.statistics');
        Route::patch('{franchise}/activate', [FranchiseController::class, 'activate'])->name('api.v1.franchises.activate');
        Route::patch('{franchise}/deactivate', [FranchiseController::class, 'deactivate'])->name('api.v1.franchises.deactivate');

        // Nested franchise documents
        Route::prefix('{franchise_id}/documents')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('api.v1.franchises.documents.index');
            Route::post('/', [DocumentController::class, 'store'])->name('api.v1.franchises.documents.store');
            Route::get('{document_id}', [DocumentController::class, 'show'])->name('api.v1.franchises.documents.show');
            Route::put('{document_id}', [DocumentController::class, 'update'])->name('api.v1.franchises.documents.update');
            Route::delete('{document_id}', [DocumentController::class, 'destroy'])->name('api.v1.franchises.documents.destroy');
            Route::get('{document_id}/download', [DocumentController::class, 'download'])->name('api.v1.franchises.documents.download');
            Route::patch('{document_id}/approve', [DocumentController::class, 'approve'])->name('api.v1.franchises.documents.approve');
            Route::patch('{document_id}/reject', [DocumentController::class, 'reject'])->name('api.v1.franchises.documents.reject');
        });

        // Nested franchise products
        Route::prefix('{franchise_id}/products')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('api.v1.franchises.products.index');
            Route::post('/', [ProductController::class, 'store'])->name('api.v1.franchises.products.store');
            Route::get('{product_id}', [ProductController::class, 'show'])->name('api.v1.franchises.products.show');
            Route::put('{product_id}', [ProductController::class, 'update'])->name('api.v1.franchises.products.update');
            Route::delete('{product_id}', [ProductController::class, 'destroy'])->name('api.v1.franchises.products.destroy');
        });
    });

    // Lead Management Routes
    // Note: Lead creation is restricted to brokers only (see brokers.php routes)
    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('api.v1.leads.index');
        Route::get('statistics', [LeadController::class, 'statistics'])->name('api.v1.leads.statistics');
        Route::get('{lead}', [LeadController::class, 'show'])->name('api.v1.leads.show');
        Route::put('{lead}', [LeadController::class, 'update'])->name('api.v1.leads.update');
        Route::delete('{lead}', [LeadController::class, 'destroy'])->name('api.v1.leads.destroy');
        Route::post('bulk-delete', [LeadController::class, 'bulkDelete'])->name('api.v1.leads.bulk-delete');
        Route::post('import-csv', [LeadController::class, 'importCsv'])->name('api.v1.leads.import-csv');
        Route::get('export-csv', [LeadController::class, 'exportCsv'])->name('api.v1.leads.export-csv');
        Route::patch('{lead}/convert', [LeadController::class, 'convert'])->name('api.v1.leads.convert');
        Route::patch('{lead}/mark-lost', [LeadController::class, 'markAsLost'])->name('api.v1.leads.mark-lost');
        Route::patch('{lead}/assign', [LeadController::class, 'assign'])->name('api.v1.leads.assign');
        Route::post('{lead}/notes', [LeadController::class, 'addNote'])->name('api.v1.leads.add-note');
    });

    // Note Management Routes
    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'index'])->name('api.v1.notes.index');
        Route::post('/', [NoteController::class, 'store'])->name('api.v1.notes.store');
        Route::get('{note}', [NoteController::class, 'show'])->name('api.v1.notes.show');
        Route::put('{note}', [NoteController::class, 'update'])->name('api.v1.notes.update');
        Route::delete('{note}', [NoteController::class, 'destroy'])->name('api.v1.notes.destroy');
        Route::delete('{note}/attachments/{attachmentIndex}', [NoteController::class, 'removeAttachment'])->name('api.v1.notes.remove-attachment');
        Route::get('{note}/attachments/{attachmentIndex}/download', [NoteController::class, 'downloadAttachment'])->name('api.v1.notes.download-attachment');
    });

    // Unit Management Routes
    Route::apiResource('units', UnitController::class);
    Route::prefix('units')->group(function () {
        Route::get('statistics', [UnitController::class, 'statistics'])->name('api.v1.units.statistics');
        Route::patch('{unit}/activate', [UnitController::class, 'activate'])->name('api.v1.units.activate');
        Route::patch('{unit}/deactivate', [UnitController::class, 'deactivate'])->name('api.v1.units.deactivate');
        Route::patch('{unit}/close', [UnitController::class, 'close'])->name('api.v1.units.close');

        // Inventory routes for a unit
        Route::prefix('{unit}/inventory')->group(function () {
            Route::get('/', [UnitInventoryController::class, 'index'])->name('api.v1.units.inventory.index');
            Route::post('{product}', [UnitInventoryController::class, 'store'])->name('api.v1.units.inventory.store');
            Route::put('{product}', [UnitInventoryController::class, 'update'])->name('api.v1.units.inventory.update');
            Route::delete('{product}', [UnitInventoryController::class, 'destroy'])->name('api.v1.units.inventory.destroy');
        });

        // Staff routes for a unit
        Route::get('{unit}/staff', [UnitController::class, 'getStaff'])->name('api.v1.units.staff');
    });

    // Task Management Routes
    Route::apiResource('tasks', TaskController::class);
    Route::prefix('tasks')->group(function () {
        Route::get('statistics', [TaskController::class, 'statistics'])->name('api.v1.tasks.statistics');
        Route::patch('{task}/complete', [TaskController::class, 'complete'])->name('api.v1.tasks.complete');
        Route::patch('{task}/start', [TaskController::class, 'start'])->name('api.v1.tasks.start');
        Route::patch('{task}/assign', [TaskController::class, 'assign'])->name('api.v1.tasks.assign');
    });

    // Technical Request Routes
    Route::prefix('technical-requests')->group(function () {
        Route::get('statistics', [TechnicalRequestController::class, 'statistics'])->name('api.v1.technical-requests.statistics');
        Route::post('bulk-delete', [TechnicalRequestController::class, 'bulkDelete'])->name('api.v1.technical-requests.bulk-delete');
    });
    Route::apiResource('technical-requests', TechnicalRequestController::class);
    Route::prefix('technical-requests')->group(function () {
        Route::patch('{technicalRequest}/assign', [TechnicalRequestController::class, 'assign'])->name('api.v1.technical-requests.assign');
        Route::post('{technicalRequest}/respond', [TechnicalRequestController::class, 'respond'])->name('api.v1.technical-requests.respond');
        Route::patch('{technicalRequest}/resolve', [TechnicalRequestController::class, 'resolve'])->name('api.v1.technical-requests.resolve');
        Route::patch('{technicalRequest}/close', [TechnicalRequestController::class, 'close'])->name('api.v1.technical-requests.close');
        Route::patch('{technicalRequest}/escalate', [TechnicalRequestController::class, 'escalate'])->name('api.v1.technical-requests.escalate');
        Route::post('{technicalRequest}/attachments', [TechnicalRequestController::class, 'addAttachment'])->name('api.v1.technical-requests.attachments');
    });

    // Transaction Management Routes
    Route::apiResource('transactions', TransactionController::class);
    Route::prefix('transactions')->group(function () {
        Route::get('statistics', [TransactionController::class, 'statistics'])->name('api.v1.transactions.statistics');
        Route::get('revenue', [TransactionController::class, 'revenue'])->name('api.v1.transactions.revenue');
        Route::get('expenses', [TransactionController::class, 'expenses'])->name('api.v1.transactions.expenses');
        Route::patch('{transaction}/complete', [TransactionController::class, 'complete'])->name('api.v1.transactions.complete');
        Route::patch('{transaction}/cancel', [TransactionController::class, 'cancel'])->name('api.v1.transactions.cancel');
        Route::post('{transaction}/refund', [TransactionController::class, 'refund'])->name('api.v1.transactions.refund');
        Route::post('{transaction}/attachments', [TransactionController::class, 'addAttachment'])->name('api.v1.transactions.attachments');
    });

    // Royalty Management Routes
    Route::apiResource('royalties', RoyaltyController::class);
    Route::prefix('royalties')->group(function () {
        Route::get('statistics', [RoyaltyController::class, 'statistics'])->name('api.v1.royalties.statistics');
        Route::get('pending', [RoyaltyController::class, 'pending'])->name('api.v1.royalties.pending');
        Route::get('overdue', [RoyaltyController::class, 'overdue'])->name('api.v1.royalties.overdue');
        Route::patch('{royalty}/mark-paid', [RoyaltyController::class, 'markAsPaid'])->name('api.v1.royalties.mark-paid');
        Route::post('{royalty}/late-fee', [RoyaltyController::class, 'calculateLateFee'])->name('api.v1.royalties.late-fee');
        Route::post('{royalty}/adjustments', [RoyaltyController::class, 'addAdjustment'])->name('api.v1.royalties.adjustments');
        Route::post('{royalty}/attachments', [RoyaltyController::class, 'addAttachment'])->name('api.v1.royalties.attachments');
        Route::post('generate-monthly', [RoyaltyController::class, 'generateMonthlyRoyalties'])->name('api.v1.royalties.generate-monthly');
    });

    // Revenue Management Routes
    Route::apiResource('revenues', RevenueController::class);
    Route::prefix('revenues')->group(function () {
        Route::get('statistics', [RevenueController::class, 'statistics'])->name('api.v1.revenues.statistics');
        Route::get('sales', [RevenueController::class, 'sales'])->name('api.v1.revenues.sales');
        Route::get('fees', [RevenueController::class, 'fees'])->name('api.v1.revenues.fees');
        Route::get('breakdown', [RevenueController::class, 'breakdown'])->name('api.v1.revenues.breakdown');
        Route::get('total-by-period', [RevenueController::class, 'totalByPeriod'])->name('api.v1.revenues.total-by-period');
        Route::patch('{revenue}/verify', [RevenueController::class, 'verify'])->name('api.v1.revenues.verify');
        Route::post('{revenue}/dispute', [RevenueController::class, 'dispute'])->name('api.v1.revenues.dispute');
        Route::post('{revenue}/refund', [RevenueController::class, 'refund'])->name('api.v1.revenues.refund');
        Route::post('{revenue}/line-items', [RevenueController::class, 'addLineItem'])->name('api.v1.revenues.line-items');
        Route::post('{revenue}/attachments', [RevenueController::class, 'addAttachment'])->name('api.v1.revenues.attachments');
    });

    // Document Management Routes
    Route::apiResource('documents', DocumentController::class);
    Route::prefix('documents')->group(function () {
        Route::get('{document}/download', [DocumentController::class, 'download'])->name('api.v1.documents.download');
    });

    // Product Management Routes
    Route::apiResource('products', ProductController::class);
    Route::prefix('products')->group(function () {
        Route::get('categories', [ProductController::class, 'categories'])->name('api.v1.products.categories');
        Route::patch('{product}/stock', [ProductController::class, 'updateStock'])->name('api.v1.products.stock');
    });

    // Review Management Routes
    Route::apiResource('reviews', ReviewController::class);
    Route::prefix('reviews')->group(function () {
        Route::get('statistics', [ReviewController::class, 'statistics'])->name('api.v1.reviews.statistics');
        Route::patch('{review}/publish', [ReviewController::class, 'publish'])->name('api.v1.reviews.publish');
        Route::patch('{review}/archive', [ReviewController::class, 'archive'])->name('api.v1.reviews.archive');
        Route::patch('{review}/notes', [ReviewController::class, 'updateNotes'])->name('api.v1.reviews.notes');
    });

    // Unit-specific review routes
    Route::prefix('units/{unit_id}/reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('api.v1.units.reviews.index');
        Route::post('/', [ReviewController::class, 'store'])->name('api.v1.units.reviews.store');
        Route::get('statistics', [ReviewController::class, 'statistics'])->name('api.v1.units.reviews.statistics');
    });
});
