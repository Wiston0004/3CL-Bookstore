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

    // Public (no login needed)
    Route::get('/books', [BookApiController::class, 'index']);
    Route::get('/books/{book}', [BookApiController::class, 'show']);

    // Protected (need token)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/books', [BookApiController::class, 'store']);
        Route::put('/books/{book}', [BookApiController::class, 'update']);
        Route::delete('/books/{book}', [BookApiController::class, 'destroy']);
    });

    // Reviews CRUD
    Route::get('/books/{book}/ratings', [ReviewController::class, 'apiRatingsSummary']);
    Route::get('/books/{book}/reviews', [ReviewController::class, 'apiList']);
    Route::put('/reviews/{review}', [ReviewController::class, 'apiUpdate']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'apiDestroy']);

    // Inventory
    Route::get('/inventory/{book}/stock', [InventoryApiController::class, 'stock']);
    Route::post('/inventory/adjust', [InventoryApiController::class, 'adjust']);

});

// JSON 404 fallback
Route::fallback(fn () => response()->json(['message' => 'Not Found'], 404));

Route::prefix('v1')->middleware('throttle:api')->group(function () {

    // --- CART (customer) ---
    Route::middleware('auth:sanctum')->name('api.cart.')->group(function () {
        Route::get   ('/cart',           [CartApiController::class, 'index'])->name('index');
        Route::post  ('/cart',           [CartApiController::class, 'store'])->name('store');     // add
        Route::patch ('/cart/{cartItem}',[CartApiController::class, 'update'])->name('update');   // change qty
        Route::delete('/cart/{cartItem}',[CartApiController::class, 'destroy'])->name('destroy'); // remove
    });

    // --- ORDERS (customer) ---
    Route::middleware('auth:sanctum')->name('api.orders.')->group(function () {
        Route::get   ('/orders',               [OrderApiController::class, 'index'])->name('index');
        Route::post  ('/orders',               [OrderApiController::class, 'store'])->name('store'); // optional: direct create by items
        Route::get   ('/orders/{order}',       [OrderApiController::class, 'show'])->name('show');
        Route::patch ('/orders/{order}',       [OrderApiController::class, 'update'])->name('update'); // update address
        Route::patch ('/orders/{order}/cancel',[OrderApiController::class, 'cancel'])->name('cancel'); // customer cancel if pending
        // checkout (create order from current cart)
        Route::post  ('/checkout',             [OrderApiController::class, 'checkout'])->name('checkout');
    });

});

Route::prefix('v1')->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/login/{type}', [AuthController::class, 'login'])->name('api.login.role');
        Route::post('/logout', [Controller::class, 'logout'])->name('api.logout');
    });

    // Staff routes (protected by auth + role:staff)
    Route::middleware(['auth:sanctum','role:staff'])->prefix('staff')->group(function () {
        Route::apiResource('events', EventController::class);
        Route::apiResource('announcements', AnnouncementController::class);
    });

    // Customer routes (protected by auth + role:customer)
    Route::middleware(['auth:sanctum','role:customer'])->group(function () {
        Route::get('/events', [EventCustomerController::class,'index'])->name('api.cust.events.index');
        Route::get('/events/{event:slug}', [EventCustomerController::class,'show'])->name('api.cust.events.show');
        Route::post('/events/{event:slug}/register', [EventCustomerController::class,'register'])->name('api.cust.events.register');

        Route::get('/announcements', [AnnouncementCustomerController::class,'index'])->name('api.cust.ann.index');
        Route::get('/announcements/{announcement}', [AnnouncementCustomerController::class,'show'])->name('api.cust.ann.show');
    });
});