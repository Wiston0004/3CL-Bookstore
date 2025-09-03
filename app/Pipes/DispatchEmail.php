<?php

namespace App\Pipes;

use Closure;
use Illuminate\Support\Facades\Mail;

class DispatchEmail {
    public function handle(object $a, Closure $next) {
        if (!in_array('mail', $a->channels ?? [])) return $next($a);
        foreach ($a->recipients as $r) {
            if (empty($r->email)) continue;
            Mail::raw($a->body, function ($m) use ($r, $a) {
                $m->to($r->email, $r->name ?? null)->subject($a->subject);
            });
        }
        return $next($a);
    }
}
