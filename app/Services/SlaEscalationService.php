<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\SlaEscalation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SlaEscalationService
{
    /**
     * Check un-actioned Hot leads past SLA deadline and trigger escalation.
     */
    public static function checkEscalations(): void
    {
        $unactionedHotLeads = Lead::withoutGlobalScopes()
            ->where('score', 'hot')
            ->where('status', 'new')
            ->where('captured_at', '<=', Carbon::now()->subMinutes(30)) // 30-minute SLA default
            ->get();

        foreach ($unactionedHotLeads as $lead) {
            $existingEscalation = SlaEscalation::where('lead_id', $lead->id)->first();
            if (!$existingEscalation) {
                SlaEscalation::create([
                    'lead_id' => $lead->id,
                    'sla_deadline' => Carbon::now(),
                    'escalated_at' => Carbon::now(),
                ]);

                Log::warning("SLA Escalation triggered for Hot Lead #{$lead->id} (Client #{$lead->client_id})");
            }
        }
    }
}
