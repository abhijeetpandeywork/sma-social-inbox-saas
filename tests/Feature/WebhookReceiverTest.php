<?php

namespace Tests\Feature;

use App\Jobs\ProcessWebhookEventJob;
use App\Models\RawEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookReceiverTest extends TestCase
{
    use RefreshDatabase;

    public function test_meta_webhook_verification_success()
    {
        $response = $this->get('/api/webhooks/meta?hub_mode=subscribe&hub_verify_token=social_inbox_secret_token&hub_challenge=99887766');

        $response->assertStatus(200);
        $this->assertEquals('99887766', $response->getContent());
    }

    public function test_meta_webhook_verification_failure()
    {
        $response = $this->get('/api/webhooks/meta?hub_mode=subscribe&hub_verify_token=wrong_token&hub_challenge=99887766');

        $response->assertStatus(403);
    }

    public function test_meta_webhook_receives_payload_and_queues_job()
    {
        Queue::fake();

        $payload = [
            'object' => 'instagram',
            'entry' => [
                [
                    'id' => '17841400000000',
                    'time' => 1700000000,
                    'changes' => [
                        [
                            'field' => 'comments',
                            'value' => [
                                'from' => ['id' => '999', 'username' => 'testuser'],
                                'media' => ['id' => '555'],
                                'id' => 'comm_123',
                                'text' => 'Bhai price kitna hai? Call me 9876543210'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/webhooks/meta', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('raw_events', [
            'platform' => 'instagram',
            'event_type' => 'comments',
            'processed' => false,
        ]);

        Queue::assertPushed(ProcessWebhookEventJob::class);
    }

    public function test_meta_webhook_event_hash_deduplication()
    {
        Queue::fake();

        $payload = [
            'object' => 'instagram',
            'entry' => [
                [
                    'id' => '17841400000000',
                    'time' => 1700000000,
                    'changes' => [
                        [
                            'field' => 'comments',
                            'value' => ['id' => 'comm_123', 'text' => 'Duplicate test']
                        ]
                    ]
                ]
            ]
        ];

        // First delivery
        $this->postJson('/api/webhooks/meta', $payload);
        $this->assertEquals(1, RawEvent::count());

        // Duplicate delivery with identical payload/entry timestamp
        $this->postJson('/api/webhooks/meta', $payload);
        $this->assertEquals(1, RawEvent::count()); // Still 1, duplicate rejected
    }
}
