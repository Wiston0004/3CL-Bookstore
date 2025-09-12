<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Api\Manager\UserController as ApiUserController;
use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\API\EventApiController;
use App\Http\Controllers\API\AnnouncementApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\PointsApiController;

Route::prefix('v1')->group(function () {

    // -------------------------------
    // Health check
    // -------------------------------
    Route::get('ping', fn () => response()->json(['ok' => true, 'time' => now()]));

    // -------------------------------
    // Auth (Sanctum)
    // -------------------------------
    Route::middleware('throttle:api')->group(function () {
        Route::post('auth/login',    [AuthController::class, 'login']);
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get ('auth/me',       [AuthController::class, 'me'])->middleware('auth:sanctum');
    });

    // -------------------------------
    // Manager-only: User JSON CRUD
    // -------------------------------
    Route::middleware(['auth:sanctum', 'role:manager', 'throttle:api'])
        ->prefix('manager')->name('api.manager.')->group(function () {
            Route::apiResource('users', ApiUserController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
        });

    // -------------------------------
    // Books
    // -------------------------------
    // Public (no login needed)
    Route::get('/books', [BookApiController::class, 'index']);
    Route::get('/books/{book}', [BookApiController::class, 'show']);

    // Protected (need token)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/books', [BookApiController::class, 'store']);
        Route::put('/books/{book}', [BookApiController::class, 'update']);
        Route::delete('/books/{book}', [BookApiController::class, 'destroy']);
    });

    // -------------------------------
    // Reviews
    // -------------------------------
    Route::get('/books/{book}/ratings', [ReviewController::class, 'apiRatingsSummary']);
    Route::get('/books/{book}/reviews', [ReviewController::class, 'apiList']);
    Route::put('/reviews/{review}', [ReviewController::class, 'apiUpdate']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'apiDestroy']);

    // -------------------------------
    // Inventory
    // -------------------------------
    Route::get('/inventory/{book}/stock', [InventoryApiController::class, 'stock']);
    Route::post('/books/{book}/decrement', [BookApiController::class, 'decrement']);

});

// -------------------------------
// JSON 404 fallback
// -------------------------------
Route::fallback(fn () => response()->json(['message' => 'Not Found'], 404));

Route::prefix('v1')->middleware('throttle:api')->group(function () {

    // -------------------------------
    // CART (customer)
    // -------------------------------
    Route::middleware('auth:sanctum')->name('api.cart.')->group(function () {
        Route::get   ('/cart',           [CartApiController::class, 'index'])->name('index');
        Route::post  ('/cart',           [CartApiController::class, 'store'])->name('store');
        Route::patch ('/cart/{cartItem}',[CartApiController::class, 'update'])->name('update');
        Route::delete('/cart/{cartItem}',[CartApiController::class, 'destroy'])->name('destroy');
    });

    // -------------------------------
    // ORDERS (customer)
    // -------------------------------
    Route::middleware('auth:sanctum')->name('api.orders.')->group(function () {
        Route::get   ('/orders',               [OrderApiController::class, 'index'])->name('index');
        Route::post  ('/orders',               [OrderApiController::class, 'store'])->name('store');
        Route::get   ('/orders/{order}',       [OrderApiController::class, 'show'])->name('show');
        Route::patch ('/orders/{order}',       [OrderApiController::class, 'update'])->name('update');
        Route::patch ('/orders/{order}/cancel',[OrderApiController::class, 'cancel'])->name('cancel');
        Route::post  ('/checkout',             [OrderApiController::class, 'checkout'])->name('checkout');
    });

    // -------------------------------
    // STAFF / MANAGER
    // -------------------------------
    Route::middleware(['auth:sanctum','role:staff,manager'])->group(function () {
        Route::get('/staff/orders', [OrderApiController::class, 'staffIndex']);
        Route::get('/staff/orders/{order}', [OrderApiController::class, 'staffShow']);

        // Redeem Points
        Route::post('/users/{user}/points/redeem', [PointsApiController::class, 'redeem'])
            ->name('api.users.points.redeem');
    });

    // -------------------------------
    // STAFF (events & announcements)
    // -------------------------------
    Route::middleware(['auth:sanctum','role:staff'])->prefix('staff')->group(function () {
        Route::apiResource('events', EventApiController::class);
        Route::apiResource('announcements', AnnouncementApiController::class);
    });

    // -------------------------------
    // CUSTOMER (events & announcements)
    // -------------------------------
    Route::middleware(['auth:sanctum','role:customer'])->group(function () {
        Route::get('/events', [EventCustomerController::class,'index'])->name('api.cust.events.index');
        Route::get('/events/{event:slug}', [EventCustomerController::class,'show'])->name('api.cust.events.show');
        Route::post('/events/{event:slug}/register', [EventCustomerController::class,'register'])->name('api.cust.events.register');

        Route::get('/announcements', [AnnouncementCustomerController::class,'index'])->name('api.cust.ann.index');
        Route::get('/announcements/{announcement}', [AnnouncementCustomerController::class,'show'])->name('api.cust.ann.show');
    });
});
