<?php

namespace App\Jobs;

use App\Services\GameCommandService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessGameCommandJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $messageData) {}

    public function handle(GameCommandService $gameCommandService): void
    {
        $gameCommandService->process($this->messageData);
    }
}
