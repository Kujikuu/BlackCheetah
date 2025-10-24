<?php

use App\Http\Controllers\Api\V1\Public\MarketplaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Marketplace Routes
|--------------------------------------------------------------------------
|
| Public marketplace routes for viewing franchise opportunities and properties.
| These routes do not require authentication.
|
*/

Route::prefix('marketplace')->group(function () {
    // Franchise opportunities
    Route::get('franchises', [MarketplaceController::class, 'getFranchises'])
        ->name('api.v1.marketplace.franchises.index');
    Route::get('franchises/{id}', [MarketplaceController::class, 'getFranchiseDetails'])
        ->name('api.v1.marketplace.franchises.show');
    
    // Available properties
    Route::get('properties', [MarketplaceController::class, 'getProperties'])
        ->name('api.v1.marketplace.properties.index');
    Route::get('properties/{id}', [MarketplaceController::class, 'getPropertyDetails'])
        ->name('api.v1.marketplace.properties.show');
    
    // Inquiry submission
    Route::post('inquiries', [MarketplaceController::class, 'submitInquiry'])
        ->name('api.v1.marketplace.inquiries.store');
});

