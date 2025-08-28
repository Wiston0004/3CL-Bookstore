<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        abort_if(!$user || !in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
