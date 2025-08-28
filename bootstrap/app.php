<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureRole;   // <-- add this import

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register route-middleware aliases (Laravel 11/12)
        $middleware->alias([
            'role' => EnsureRole::class,   // now you can use ->middleware('role:manager')
        ]);

        // (Optional) if you created your own versions of these and want to alias them:
        // use App\Http\Middleware\Authenticate;
        // use App\Http\Middleware\RedirectIfAuthenticated;
        // $middleware->alias([
        //     'auth'  => Authenticate::class,
        //     'guest' => RedirectIfAuthenticated::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
