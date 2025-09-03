<?php

namespace App\Pipes;

use Closure;
use Illuminate\Support\Facades\Log;

class DispatchPush {
    public function handle(object $a, Closure $next) {
        if (!in_array('push', $a->channels ?? [])) return $next($a);
        Log::info("PUSH broadcast: {$a->subject}");
        return $next($a);
    }
}
