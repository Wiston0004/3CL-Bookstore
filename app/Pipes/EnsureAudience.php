<?php

namespace App\Pipes;

use Closure;
use Illuminate\Validation\ValidationException;

class EnsureAudience {
    public function handle(object $a, Closure $next) {
        $count = is_countable($a->recipients) ? count($a->recipients)
               : (method_exists($a->recipients,'count') ? $a->recipients->count() : 0);
        if ($count === 0) {
            throw ValidationException::withMessages(['recipients' => 'No recipients found.']);
        }
        if (empty($a->title) || empty($a->message)) {
            throw ValidationException::withMessages(['message' => 'Title and message are required.']);
        }
        return $next($a);
    }
}
