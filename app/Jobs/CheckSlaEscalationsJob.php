<?php

namespace App\Jobs;

use App\Services\PlatformConnectionService;
use App\Services\SlaEscalationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckSlaEscalationsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // 1. Check SLA Escalations for un-actioned Hot leads
        SlaEscalationService::checkEscalations();

        // 2. Check Platform OAuth Token Expirations
        PlatformConnectionService::checkTokenHealth();
    }
}
