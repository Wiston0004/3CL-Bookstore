<?php

namespace App\Jobs;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;   // ✅ for API calls
use Illuminate\Support\Facades\Log;

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
        $reg = EventRegistration::with(['user', 'event'])->find($this->registrationId);

        if ($reg && $reg->user && $reg->event) {
            $user   = $reg->user;
            $points = $reg->event->points_reward ?? 0;

            if ($points > 0 && !$reg->awarded_points) {
                try {
                    // ✅ Call your Points API as a web service
                    $response = Http::asJson()->post(
                        "http://localhost/3CL-Bookstore/public/api/v1/users/{$user->id}/points/add",
                        ['points' => $points]
                    );

                    if ($response->successful()) {
                        $reg->update([
                            'awarded_points' => $points,
                            'awarded_at'     => now(),
                        ]);
                    } else {
                        Log::warning("Failed to award points via API", [
                            'user_id'  => $user->id,
                            'points'   => $points,
                            'response' => $response->body(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error("Error awarding points via API", [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
