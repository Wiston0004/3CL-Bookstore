<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleLoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Manager\UserAdminController;

Route::view('/', 'home')->name('home'); // homepage with Register + 3 Logins

// registration (customer)
Route::get('/register',[RegisterController::class,'show'])->name('register');
Route::post('/register',[RegisterController::class,'store']);

// three login pages
Route::get('/login/manager',[RoleLoginController::class,'showManager'])->name('login.manager');
Route::get('/login/staff',[RoleLoginController::class,'showStaff'])->name('login.staff');
Route::get('/login/customer',[RoleLoginController::class,'showCustomer'])->name('login.customer');
Route::post('/login/{type}',[RoleLoginController::class,'login'])->name('login.role'); // {type}=manager|staff|customer
Route::post('/logout',[RoleLoginController::class,'logout'])->name('logout');

// dashboards
Route::middleware(['auth','role:manager'])->group(function(){
    Route::view('/manager','dashboards.manager')->name('manager.dashboard');
    Route::resource('/manager/users',UserAdminController::class)->names('manager.users');
});

Route::middleware(['auth','role:staff'])->group(function(){
    Route::view('/staff','dashboards.staff')->name('staff.dashboard');
});

Route::middleware(['auth','role:customer'])->group(function(){
    Route::view('/customer','dashboards.customer')->name('customer.dashboard');
});

// profile
Route::middleware('auth')->group(function(){
    Route::get('/profile',[ProfileController::class,'edit'])->name('profile.edit');
    Route::post('/profile',[ProfileController::class,'update'])->name('profile.update');
});
