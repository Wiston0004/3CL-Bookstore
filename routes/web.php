<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleLoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Manager\UserAdminController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::view('/', 'home')->name('home');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/login/manager',  [RoleLoginController::class, 'showManager'])->name('login.manager');
Route::get('/login/staff',    [RoleLoginController::class, 'showStaff'])->name('login.staff');
Route::get('/login/customer', [RoleLoginController::class, 'showCustomer'])->name('login.customer');
Route::post('/login/{type}',  [RoleLoginController::class, 'login'])->name('login.role'); // {type}=manager|staff|customer
Route::post('/logout',        [RoleLoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboards
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::view('/', 'dashboards.manager')->name('dashboard');

    // Users (no destroy, no bulk)
    Route::resource('users', UserAdminController::class)
        ->only(['index','create','store','edit','update'])
        ->names('users');

    // Optional CSV export
    Route::get('users/export', [UserAdminController::class, 'export'])->name('users.export');

    // Report
    Route::get('reports/customers', [ReportController::class, 'customersMonthly'])->name('reports.customers');
});

Route::middleware(['auth','role:staff'])->group(function () {
    Route::view('/staff', 'dashboards.staff')->name('staff.dashboard');

    // Back-compat alias for old code using dashboard.staff
    Route::redirect('/dashboard/staff', '/staff')->name('dashboard.staff');
});

Route::middleware(['auth','role:customer'])->group(function () {
    Route::view('/customer', 'dashboards.customer')->name('customer.dashboard');
});

/*
|--------------------------------------------------------------------------
| Profile (blocked for manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:staff,customer'])->group(function () {
    Route::get('/profile', [ProfileController::class,'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class,'update'])->name('profile.update');

    // Cart
    Route::get('/cart',               [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add',          [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{cartItem}',  [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout & orders
    Route::get( '/checkout',                [OrderController::class, 'showCheckout'])->name('checkout.show');
    Route::post('/checkout',                [OrderController::class, 'checkout'])->name('checkout');
    Route::get( '/orders',                  [OrderController::class, 'index'])->name('orders.index');
    Route::get( '/orders/{order}',          [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/address', [OrderController::class, 'updateAddress'])->name('orders.address');
});

// Order state transitions: staff or manager only
Route::middleware(['auth','role:staff,manager'])->group(function () {
    Route::patch('/orders/{order}/ship',     [OrderController::class, 'ship'])->name('orders.ship');
    Route::patch('/orders/{order}/arrive',   [OrderController::class, 'arrive'])->name('orders.arrive');
    Route::patch('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::patch('/orders/{order}/cancel',   [OrderController::class, 'cancel'])->name('orders.cancel');
});

/*
|--------------------------------------------------------------------------
| Public catalog
|--------------------------------------------------------------------------
*/
Route::get('/catalog',        [BookController::class, 'customerIndex'])->name('customer.index');
Route::get('/catalog/{book}', [BookController::class, 'customerShow'])->name('customer.show');

// Reviews
Route::middleware('auth')->group(function () {
    Route::post('/catalog/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

/*
|--------------------------------------------------------------------------
| Books CRUD & Inventory (staff or manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:staff,manager'])->group(function () {
    Route::resource('books', BookController::class);
    Route::post('books/{book}/stock',   [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get( 'books/{book}/history', [InventoryController::class, 'history'])->name('inventory.history');
});
