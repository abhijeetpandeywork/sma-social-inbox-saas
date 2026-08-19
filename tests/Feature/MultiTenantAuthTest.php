<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Lead;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_admin_can_access_all_client_leads()
    {
        $clientA = Client::create(['name' => 'Client A', 'agency_id' => 1]);
        $clientB = Client::create(['name' => 'Client B', 'agency_id' => 1]);

        Lead::create(['client_id' => $clientA->id, 'platform' => 'instagram', 'contact_name' => 'Lead A']);
        Lead::create(['client_id' => $clientB->id, 'platform' => 'facebook', 'contact_name' => 'Lead B']);

        $admin = TeamMember::create([
            'name' => 'Admin User',
            'email' => 'admin@agency.com',
            'password' => 'secret123',
            'role' => 'Agency Admin',
            'assigned_clients' => [],
        ]);

        $this->actingAs($admin);

        $leads = Lead::all();
        $this->assertCount(2, $leads);
    }

    public function test_team_executive_scoped_to_assigned_clients()
    {
        $clientA = Client::create(['name' => 'Client A', 'agency_id' => 1]);
        $clientB = Client::create(['name' => 'Client B', 'agency_id' => 1]);

        Lead::create(['client_id' => $clientA->id, 'platform' => 'instagram', 'contact_name' => 'Lead A']);
        Lead::create(['client_id' => $clientB->id, 'platform' => 'facebook', 'contact_name' => 'Lead B']);

        $executive = TeamMember::create([
            'name' => 'Exec User',
            'email' => 'exec@agency.com',
            'password' => 'secret123',
            'role' => 'Team Executive',
            'assigned_clients' => [$clientA->id], // Assigned to Client A only
        ]);

        $this->actingAs($executive);

        $leads = Lead::all();
        $this->assertCount(1, $leads);
        $this->assertEquals('Lead A', $leads->first()->contact_name);
    }

    public function test_lead_contact_phone_is_encrypted_at_field_level()
    {
        $client = Client::create(['name' => 'Client Encrypt', 'agency_id' => 1]);

        $lead = Lead::create([
            'client_id' => $client->id,
            'platform' => 'instagram',
            'contact_phone' => '+919876543210',
            'contact_name' => 'Encrypted Lead',
        ]);

        // Model attribute decryption works naturally
        $this->assertEquals('+919876543210', $lead->contact_phone);

        // Verify raw DB string is encrypted (does not contain plaintext number)
        $rawDbRecord = \Illuminate\Support\Facades\DB::table('leads')->where('id', $lead->id)->first();
        $this->assertStringNotContainsString('+919876543210', $rawDbRecord->contact_phone);
    }
}
