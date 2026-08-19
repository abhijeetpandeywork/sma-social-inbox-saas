<?php

namespace Tests\Unit;

use App\Jobs\ProcessWebhookEventJob;
use App\Models\FailedAction;
use App\Models\RawEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueWorkerSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_has_5_retry_attempts_and_escalating_backoff()
    {
        $job = new ProcessWebhookEventJob(1);

        $this->assertEquals(5, $job->tries);
        $this->assertEquals([30, 120, 600, 1800, 3600], $job->backoff());
    }

    public function test_job_failure_logs_to_failed_actions_dead_letter_table()
    {
        $rawEvent = RawEvent::create([
            'client_id' => 42,
            'platform' => 'facebook',
            'event_type' => 'messages',
            'event_hash' => 'hash_test_fail_123',
            'payload_json' => ['test' => 'data'],
        ]);

        $job = new ProcessWebhookEventJob($rawEvent->id);

        $exception = new \Exception("Platform API rate limit exceeded");
        $job->failed($exception);

        $this->assertDatabaseHas('failed_actions', [
            'client_id' => 42,
            'action_type' => 'process_webhook',
        ]);

        $failedAction = FailedAction::first();
        $this->assertStringContainsString("Platform API rate limit exceeded", $failedAction->exception);
    }
}
