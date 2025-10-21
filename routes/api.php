<?php

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

// Sanctum user route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Load API version 1 routes
Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/v1/auth.php';
    require __DIR__ . '/api/v1/shared.php';
    require __DIR__ . '/api/v1/resources.php';
    require __DIR__ . '/api/v1/admin.php';
    require __DIR__ . '/api/v1/franchisor.php';
    require __DIR__ . '/api/v1/franchisee.php';
    require __DIR__ . '/api/v1/sales.php';
});