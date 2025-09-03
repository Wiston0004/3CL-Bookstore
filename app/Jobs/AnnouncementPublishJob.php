<?php

namespace App\Jobs;

use App\Services\AnnouncementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnnouncementPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $announcementId, public array $payload) {}
    public $tries=3; public $backoff=10;

    public function handle(AnnouncementService $service): void
    {
        $service->publish($this->announcementId, $this->payload);
    }
}
