<?php

namespace App\Jobs;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AwardPointsForRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $registrationId) {}
    public $tries=3; public $backoff=10;

    public function handle(): void
    {
        DB::transaction(function () {
            $reg = EventRegistration::with(['event','user'])->lockForUpdate()->find($this->registrationId);
            if (!$reg || !$reg->user || !$reg->event) return;
            if ($reg->awarded_at) return; // idempotent
            $pts = (int)($reg->event->points_reward ?? 0);
            if ($pts <= 0) return;
            $reg->user->increment('points', $pts);
            $reg->update(['awarded_points'=>$pts,'awarded_at'=>now()]);
        });
    }
}
