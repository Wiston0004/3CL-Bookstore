<?php

namespace App\Pipes;

use Closure;

class FormatMessage {
    public function handle(object $a, Closure $next) {
        $footer = isset($a->meta['event_id']) ? "\n\n[Event #{$a->meta['event_id']}]" : '';
        $a->subject = $a->title;
        $a->body    = trim(($a->message ?? '').$footer);
        return $next($a);
    }
}
