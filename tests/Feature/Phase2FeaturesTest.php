<?php

namespace Tests\Feature;

use App\Jobs\CheckSlaEscalationsJob;
use App\Models\ActionLog;
use App\Models\Client;
use App\Models\Lead;
use App\Models\SlaEscalation;
use App\Models\TeamMember;
use App\Services\TwitterService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2FeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_twitter_service_usage_cost_tracking()
    {
        $client = Client::create(['name' => 'Twitter Test Client']);

        $costRead = TwitterService::recordUsage($client->id, 'read_tweet');
        $costWrite = TwitterService::recordUsage($client->id, 'send_dm');

        $this->assertEquals(0.005, $costRead);
        $this->assertEquals(0.015, $costWrite);

        $this->assertDatabaseHas('action_log', [
            'client_id' => $client->id,
            'platform' => 'twitter',
            'action_type' => 'send_dm',
        ]);
    }

    public function test_check_sla_escalations_job_triggers_escalation_for_hot_lead()
    {
        $client = Client::create(['name' => 'SLA Client']);

        $hotLead = Lead::create([
            'client_id' => $client->id,
            'platform' => 'instagram',
            'status' => 'new',
            'score' => 'hot',
            'captured_at' => Carbon::now()->subMinutes(40), // Past 30-min SLA deadline
        ]);

        $job = new CheckSlaEscalationsJob();
        $job->handle();

        $this->assertDatabaseHas('sla_escalations', [
            'lead_id' => $hotLead->id,
        ]);
    }

    public function test_database_seeder_populates_rbac_users_and_clients()
    {
        $this->seed();

        $this->assertDatabaseHas('team_members', ['email' => 'admin@digitalrubix.com', 'role' => 'Agency Admin']);
        $this->assertDatabaseHas('team_members', ['email' => 'manager@digitalrubix.com', 'role' => 'Client Manager']);
        $this->assertDatabaseHas('team_members', ['email' => 'exec@digitalrubix.com', 'role' => 'Team Executive']);

        $this->assertDatabaseHas('clients', ['name' => 'Sai Business Solutions']);
        $this->assertDatabaseHas('leads', ['contact_name' => 'Rahul Sharma', 'score' => 'hot']);
    }
}
