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

    // ----------------- STAFF API -----------------
    Route::middleware(['auth:sanctum','role:staff'])->group(function () {
        // Events CRUD
        Route::get('/events', [EventApiController::class,'index']);
        Route::post('/events', [EventApiController::class,'store']);
        Route::get('/events/{event}', [EventApiController::class,'show']);
        Route::put('/events/{event}', [EventApiController::class,'update']);
        Route::delete('/events/{event}', [EventApiController::class,'destroy']);

        // Announcements CRUD
        Route::get('/announcements', [AnnouncementApiController::class,'index']);
        Route::post('/announcements', [AnnouncementApiController::class,'store']);
        Route::get('/announcements/{announcement}', [AnnouncementApiController::class,'show']);
        Route::put('/announcements/{announcement}', [AnnouncementApiController::class,'update']);
        Route::delete('/announcements/{announcement}', [AnnouncementApiController::class,'destroy']);
    });

    // ----------------- CUSTOMER API -----------------
    Route::middleware(['auth:sanctum','role:customer'])->group(function () {
        Route::get('/events', [EventApiController::class,'index']);
        Route::get('/events/{event}', [EventApiController::class,'show']);
        Route::post('/events/{event}/register', [EventApiController::class,'register']);

        Route::get('/announcements', [AnnouncementApiController::class,'index']);
        Route::get('/announcements/{announcement}', [AnnouncementApiController::class,'show']);
    });
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