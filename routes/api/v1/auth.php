<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\OnboardingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Authentication Routes
|--------------------------------------------------------------------------
|
| Here are the authentication-related routes for API version 1.
| These routes handle login, logout, and user onboarding.
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.v1.register');
});

// Protected routes (authentication required)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');
    });

    // Onboarding routes (for franchisees)
    Route::prefix('onboarding')->group(function () {
        Route::get('/status', [OnboardingController::class, 'checkOnboardingStatus'])->name('api.v1.onboarding.status');
        Route::post('/complete', [OnboardingController::class, 'completeProfile'])->name('api.v1.onboarding.complete');
    });
});
