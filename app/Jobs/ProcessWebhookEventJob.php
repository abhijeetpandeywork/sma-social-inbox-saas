<?php

namespace App\Jobs;

use App\Models\ActionLog;
use App\Models\FailedAction;
use App\Models\RawEvent;
use App\Services\AutoReplyService;
use App\Services\LeadDetectionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessWebhookEventJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max retry attempts before moving to dead-letter (failed_actions).
     */
    public int $tries = 5;

    public int $rawEventId;

    public function __construct(int $rawEventId)
    {
        $this->rawEventId = $rawEventId;
    }

    /**
     * Escalating backoff delays in seconds: 30s, 2m, 10m, 30m, 1h
     */
    public function backoff(): array
    {
        return [30, 120, 600, 1800, 3600];
    }

    public function handle(): void
    {
        $rawEvent = RawEvent::find($this->rawEventId);
        if (!$rawEvent) {
            return;
        }

        $payload = $rawEvent->payload_json ?? [];
        $clientId = $rawEvent->client_id ?? 1;

        // Process webhook changes
        if (isset($payload['changes']) && is_array($payload['changes'])) {
            foreach ($payload['changes'] as $change) {
                $value = $change['value'] ?? [];
                if (!empty($value['text'])) {
                    $lead = LeadDetectionService::processIncomingComment($clientId, $rawEvent->platform, $value);
                    if ($lead) {
                        AutoReplyService::handleLeadAction($lead, $value);
                    }
                }
            }
        }

        $rawEvent->update(['processed' => true]);

        ActionLog::create([
            'client_id' => $rawEvent->client_id,
            'platform' => $rawEvent->platform,
            'action_type' => 'process_webhook',
            'target_id' => (string) $rawEvent->id,
            'status' => 'success',
            'attempt_count' => $this->attempts(),
        ]);
    }

    /**
     * Handle job failure after max attempts (Dead-letter handler)
     */
    public function failed(Throwable $exception): void
    {
        $rawEvent = RawEvent::find($this->rawEventId);

        FailedAction::create([
            'uuid' => $this->job ? $this->job->getJobId() : null,
            'connection' => $this->connection ?? 'database',
            'queue' => $this->queue ?? 'default',
            'payload' => json_encode(['raw_event_id' => $this->rawEventId]),
            'exception' => $exception->getMessage() . "\n" . $exception->getTraceAsString(),
            'client_id' => $rawEvent ? $rawEvent->client_id : null,
            'action_type' => 'process_webhook',
            'attempt_count' => $this->attempts(),
        ]);

        ActionLog::create([
            'client_id' => $rawEvent ? $rawEvent->client_id : null,
            'platform' => $rawEvent ? $rawEvent->platform : 'unknown',
            'action_type' => 'process_webhook',
            'target_id' => (string) $this->rawEventId,
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'attempt_count' => $this->attempts(),
        ]);

        Log::error("Job failed after max attempts for raw_event_id {$this->rawEventId}: {$exception->getMessage()}");
    }
}
