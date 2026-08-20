<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Lead;
use App\Models\PlatformConnection;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SetupWizardAndClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected TeamMember $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = TeamMember::create([
            'agency_id' => 1,
            'name' => 'Agency Admin',
            'email' => 'admin@digitalrubix.com',
            'password' => Hash::make('password'),
            'role' => 'Agency Admin',
            'assigned_clients' => [],
            'two_factor_enabled' => false,
        ]);
    }

    public function test_setup_wizard_renders_step_1(): void
    {
        $response = $this->actingAs($this->admin)->get('/setup?step=1');
        $response->assertStatus(200);
        $response->assertSee('Step 1: Set Up Administrator Account');
    }

    public function test_setup_wizard_step_2_creates_business(): void
    {
        $response = $this->actingAs($this->admin)->post('/setup/step2', [
            'business_name' => 'Apex Realty',
            'data_retention_months' => 12,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', ['name' => 'Apex Realty']);
    }

    public function test_setup_wizard_step_3_saves_platform_connection(): void
    {
        $client = Client::create([
            'agency_id' => 1,
            'name' => 'Apex Realty',
            'status' => 'active',
            'data_retention_months' => 12,
        ]);

        $response = $this->actingAs($this->admin)->post('/setup/step3', [
            'client_id' => $client->id,
            'platform' => 'instagram',
            'platform_account_id' => '1784140999999',
            'access_token' => 'EAAG_test_token_123',
            'token_expires_in_days' => 60,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('platform_connections', [
            'client_id' => $client->id,
            'platform' => 'instagram',
            'platform_account_id' => '1784140999999',
        ]);
    }

    public function test_client_crud_management(): void
    {
        $response = $this->actingAs($this->admin)->post('/clients', [
            'name' => 'Bagnomy Leather',
            'status' => 'active',
            'data_retention_months' => 24,
        ]);

        $response->assertRedirect('/clients');
        $this->assertDatabaseHas('clients', ['name' => 'Bagnomy Leather']);

        $client = Client::where('name', 'Bagnomy Leather')->first();
        $response = $this->actingAs($this->admin)->get('/clients');
        $response->assertStatus(200);
        $response->assertSee('Bagnomy Leather');
    }

    public function test_team_member_management(): void
    {
        $response = $this->actingAs($this->admin)->post('/team', [
            'name' => 'Jane Doe',
            'email' => 'jane@digitalrubix.com',
            'password' => 'secret123',
            'role' => 'Team Executive',
        ]);

        $response->assertRedirect('/team');
        $this->assertDatabaseHas('team_members', ['email' => 'jane@digitalrubix.com']);
    }

    public function test_lead_simulation_endpoint(): void
    {
        $client = Client::create([
            'agency_id' => 1,
            'name' => 'Apex Realty',
            'status' => 'active',
            'data_retention_months' => 12,
        ]);

        $response = $this->actingAs($this->admin)->post('/leads/simulate', [
            'client_id' => $client->id,
            'platform' => 'instagram',
            'contact_name' => 'Rohan Sharma',
            'contact_phone' => '+91 99887 76655',
            'comment_text' => 'Bhai price batao urgent buy karna hai',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'client_id' => $client->id,
            'contact_name' => 'Rohan Sharma',
            'score' => 'hot',
        ]);
    }

    public function test_reset_demo_data_command(): void
    {
        $client = Client::create([
            'agency_id' => 1,
            'name' => 'Temporary Demo Client',
            'status' => 'active',
            'data_retention_months' => 12,
        ]);

        Lead::create([
            'client_id' => $client->id,
            'platform' => 'instagram',
            'contact_name' => 'Temp',
            'status' => 'new',
            'score' => 'warm',
        ]);

        Artisan::call('app:reset-demo-data');

        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseHas('team_members', ['email' => 'admin@digitalrubix.com']);
    }

    public function test_guide_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/guide');
        $response->assertStatus(200);
        $response->assertSee('Step-by-Step System Setup Guide');
    }
}
