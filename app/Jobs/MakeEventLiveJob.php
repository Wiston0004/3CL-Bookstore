<?php

namespace App\Jobs;

use App\Services\EventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Pipeline\Pipeline;

class MakeEventLiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $eventId, public array $announcementPayload=[]) {}
    public $tries=3; public $backoff=10;

    public function handle(EventService $svc): void
    {
        $svc->goLive($this->eventId);

        if ($this->announcementPayload) {
            app(Pipeline::class)->send((object)$this->announcementPayload)
                ->through([
                    \App\Pipes\EnsureAudience::class,
                    \App\Pipes\FormatMessage::class,
                    \App\Pipes\DispatchEmail::class,
                    \App\Pipes\DispatchSms::class,
                    \App\Pipes\DispatchPush::class,
                ])->thenReturn();
        }
    }
}
