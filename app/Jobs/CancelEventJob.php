<?php

namespace App\Jobs;

use App\Services\EventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $eventId, public ?string $reason=null) {}
    public $tries=3; public $backoff=10;
    public function handle(EventService $svc): void { $svc->cancel($this->eventId, $this->reason); }
}
