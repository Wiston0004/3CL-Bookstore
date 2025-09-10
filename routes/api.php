<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Manager\UserController as ApiUserController;

Route::prefix('v1')->group(function () {

    // Health check
    Route::get('ping', fn () => response()->json(['ok' => true, 'time' => now()]));

    // Auth (Sanctum)
    Route::middleware('throttle:api')->group(function () {
        Route::post('auth/login',    [AuthController::class, 'login']);
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get ('auth/me',       [AuthController::class, 'me'])->middleware('auth:sanctum');
    });

    // Manager-only: User JSON CRUD (NO trashed/restore)
    Route::middleware(['auth:sanctum', 'role:manager', 'throttle:api'])
        ->prefix('manager')->name('api.manager.')->group(function () {

        Route::apiResource('users', ApiUserController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy']);
    });
});

// JSON 404 fallback
Route::fallback(fn () => response()->json(['message' => 'Not Found'], 404));
