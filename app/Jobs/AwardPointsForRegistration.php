<?php

namespace App\Jobs;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

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
            $user   = $reg->user;
            $points = $reg->event->points_reward ?? 0;

            if ($points > 0 && !$reg->awarded_points) {
                $success = false;

                try {
                    // 🔹 Try external API first
                    $base = rtrim(config('services.users_api.base'), '/');
                    $timeout = (float) config('services.users_api.timeout', 5);

                    $res = Http::timeout($timeout)
                        ->acceptJson()
                        ->post("$base/users/{$user->id}/points/add", [
                            'points' => $points,
                        ]);

                    if ($res->ok()) {
                        $success = true;
                    }
                } catch (\Throwable $e) {
                    $success = false;
                }

                if (!$success) {
                    // 🔹 Fallback to internal DB update
                    $user->increment('points', $points);
                }

                // mark registration as awarded regardless of method
                $reg->update([
                    'awarded_points' => $points,
                    'awarded_at'     => now(),
                ]);
            }
        }
    }
}
