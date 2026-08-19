<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PlatformConnection;
use App\Models\TeamMember;
use App\Services\LinkedInAlertService;
use App\Services\YouTubeAndGmbPollingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase3And4FeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_youtube_and_gmb_polling_service()
    {
        $client = Client::create(['name' => 'YouTube Client']);
        $ytConn = PlatformConnection::create([
            'client_id' => $client->id,
            'platform' => 'youtube',
            'access_token' => 'token_yt',
            'health_status' => 'healthy',
        ]);

        $count = YouTubeAndGmbPollingService::pollYouTubeComments($ytConn);
        $this->assertEquals(1, $count);
        $this->assertNotNull($ytConn->fresh()->last_successful_call_at);
    }

    public function test_linkedin_alert_creation()
    {
        $client = Client::create(['name' => 'LinkedIn Client']);
        $alert = LinkedInAlertService::createAlert($client->id, 'post_mention', 'https://linkedin.com/post/123');

        $this->assertDatabaseHas('linkedin_alerts', [
            'client_id' => $client->id,
            'alert_type' => 'post_mention',
            'status' => 'unread',
        ]);
    }

    public function test_lead_quality_report_endpoint()
    {
        $client = Client::create(['name' => 'Report Client']);
        $user = TeamMember::create([
            'name' => 'Report User',
            'email' => 'report@test.com',
            'password' => 'secret123',
            'role' => 'Agency Admin',
        ]);

        $this->actingAs($user);

        $response = $this->get('/reports/lead-quality?client_id=' . $client->id);
        $response->assertStatus(200);
        $response->assertSee('Weekly Lead Quality Report');
    }

    public function test_backup_database_command_execution()
    {
        $exitCode = Artisan::call('app:backup-database');
        $this->assertEquals(0, $exitCode);
    }
}
