<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformConnectionUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_connections_page_renders_and_stores_platform_token()
    {
        $client = Client::create(['name' => 'Test Client']);
        $user = TeamMember::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => 'secret123',
            'role' => 'Agency Admin',
        ]);

        $this->actingAs($user);

        // Render Connections Page
        $response = $this->get('/connections');
        $response->assertStatus(200);
        $response->assertSee('Platform Connections');
        $response->assertSee('Step-by-Step Webhook');

        // Store Token via Form
        $postResponse = $this->post('/connections', [
            'client_id' => $client->id,
            'platform' => 'instagram',
            'platform_account_id' => '17841400000000',
            'access_token' => 'EAAG...test_encrypted_token',
            'token_expires_in_days' => 60,
        ]);

        $postResponse->assertRedirect();
        $this->assertDatabaseHas('platform_connections', [
            'client_id' => $client->id,
            'platform' => 'instagram',
            'platform_account_id' => '17841400000000',
        ]);
    }
}
