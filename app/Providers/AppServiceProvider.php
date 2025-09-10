<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Named limiter used by 'throttle:api'
        RateLimiter::for('api', function (Request $request) {
            // 60 requests / minute per user id (or IP if guest)
            return [
                Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        \Illuminate\Support\Facades\Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));

        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}
