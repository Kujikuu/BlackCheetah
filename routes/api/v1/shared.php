<?php

use App\Http\Controllers\Api\V1\Shared\AccountSettingsController;
use App\Http\Controllers\Api\V1\Resources\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Shared Routes
|--------------------------------------------------------------------------
|
| Here are the shared authenticated routes for API version 1.
| These routes are available to all authenticated users regardless of role.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Account Settings routes (available for all authenticated users)
    Route::prefix('account')->group(function () {
        Route::get('/profile', [AccountSettingsController::class, 'getProfile'])->name('api.v1.account.profile');
        Route::put('/profile', [AccountSettingsController::class, 'updateProfile'])->name('api.v1.account.profile.update');
        Route::put('/password', [AccountSettingsController::class, 'updatePassword'])->name('api.v1.account.password.update');
        Route::post('/avatar', [AccountSettingsController::class, 'uploadAvatar'])->name('api.v1.account.avatar.upload');
        Route::delete('/avatar', [AccountSettingsController::class, 'deleteAvatar'])->name('api.v1.account.avatar.delete');
        Route::put('/notifications', [AccountSettingsController::class, 'updateNotifications'])->name('api.v1.account.notifications.update');
    });

    // Notification Management Routes (available for all authenticated users)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
        Route::get('/stats', [NotificationController::class, 'stats'])->name('api.v1.notifications.stats');
        Route::get('/{id}', [NotificationController::class, 'show'])->name('api.v1.notifications.show');
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.v1.notifications.mark-read');
        Route::patch('/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('api.v1.notifications.mark-unread');
        Route::patch('/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead'])->name('api.v1.notifications.mark-multiple-read');
        Route::patch('/mark-multiple-unread', [NotificationController::class, 'markMultipleAsUnread'])->name('api.v1.notifications.mark-multiple-unread');
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.v1.notifications.mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('api.v1.notifications.destroy');
    });
});
