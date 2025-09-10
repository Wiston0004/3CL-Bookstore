<?php

namespace App\Jobs;

use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AwardPointsForRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $registrationId;

    public function __construct(int $registrationId)
    {
        $this->registrationId = $registrationId;
    }

    public function handle(): void
    {
        $reg = EventRegistration::find($this->registrationId);
        if ($reg && $reg->user && $reg->event) {
            $user = $reg->user;
            $points = $reg->event->points_reward ?? 0;

            if ($points > 0 && !$reg->awarded_points) {
                $user->increment('points', $points);
                $reg->update([
                    'awarded_points' => $points,
                    'awarded_at' => now(),
                ]);
            }
        }
    }
}
