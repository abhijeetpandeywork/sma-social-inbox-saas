<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Lead;
use App\Models\PiiAccessLog;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardUiAndHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_json_status()
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'database',
            'cron_queue' => [
                'status',
                'last_successful_run_at',
            ],
            'failed_actions_count',
        ]);
    }

    public function test_dashboard_renders_and_pii_view_logs_access()
    {
        $client = Client::create(['name' => 'Test Client']);
        $user = TeamMember::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => 'secret123',
            'role' => 'Client Manager',
            'assigned_clients' => [$client->id],
        ]);

        $lead = Lead::create([
            'client_id' => $client->id,
            'platform' => 'instagram',
            'contact_name' => 'John Doe',
            'contact_phone' => '+919876543210',
            'status' => 'new',
            'score' => 'hot',
        ]);

        $this->actingAs($user);

        // Dashboard test
        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('John Doe');

        // PII Detail view test
        $leadResponse = $this->get("/leads/{$lead->id}");
        $leadResponse->assertStatus(200);
        $leadResponse->assertSee('+919876543210');

        // Assert PII Access log entry created
        $this->assertDatabaseHas('pii_access_log', [
            'user_id' => $user->id,
            'lead_id' => $lead->id,
            'action' => 'view_pii',
        ]);
    }
}
