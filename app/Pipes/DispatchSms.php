<?php

namespace App\Pipes;

use Closure;
use Illuminate\Support\Facades\Log;

class DispatchSms {
    public function handle(object $a, Closure $next) {
        if (!in_array('sms', $a->channels ?? [])) return $next($a);
        foreach ($a->recipients as $r) {
            if (empty($r->phone)) continue;
            Log::info("SMS to {$r->phone}: {$a->subject} - {$a->body}");
        }
        return $next($a);
    }
}
