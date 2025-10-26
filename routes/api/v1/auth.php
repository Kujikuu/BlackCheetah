<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\OnboardingController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Authentication Routes
|--------------------------------------------------------------------------
|
| Here are the authentication-related routes for API version 1.
| These routes handle login, logout, password reset, email verification,
| and user onboarding.
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    // Login with rate limiting
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('api.v1.login');

    // Registration with rate limiting
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:registration')
        ->name('api.v1.register');

    // Password reset routes with rate limiting
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
        ->middleware('throttle:password-reset')
        ->name('api.v1.password.forgot');

    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:password-reset')
        ->name('api.v1.password.reset');

    Route::post('/validate-reset-token', [PasswordResetController::class, 'validateResetToken'])
        ->name('api.v1.password.validate-token');

    // Email verification - signed URL route (no auth required but must be signed)
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify'); // Laravel expects this exact name
});

// Protected routes (authentication required)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');

        // Email verification routes with rate limiting
        Route::post('/email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail'])
            ->middleware('throttle:email-verification')
            ->name('verification.send');

        Route::get('/email/verification-status', [EmailVerificationController::class, 'checkVerificationStatus'])
            ->name('verification.status');
    });

    // Onboarding routes (for franchisees)
    Route::prefix('onboarding')->group(function () {
        Route::get('/status', [OnboardingController::class, 'checkOnboardingStatus'])->name('api.v1.onboarding.status');
        Route::post('/complete', [OnboardingController::class, 'completeProfile'])->name('api.v1.onboarding.complete');
        Route::get('/franchise-status', [OnboardingController::class, 'checkFranchiseStatus'])->name('api.v1.onboarding.franchise-status');
    });
});
