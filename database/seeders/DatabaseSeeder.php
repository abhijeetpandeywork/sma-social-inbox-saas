<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use App\Models\Client;
use App\Models\Lead;
use App\Models\PlatformConnection;
use App\Models\PlatformHealth;
use App\Models\TeamMember;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Clients
        $sai = Client::create([
            'agency_id' => 1,
            'name' => 'Sai Business Solutions',
            'status' => 'active',
            'data_retention_months' => 12,
        ]);

        $bagnomy = Client::create([
            'agency_id' => 1,
            'name' => 'Bagnomy',
            'status' => 'active',
            'data_retention_months' => 12,
        ]);

        $rubix = Client::create([
            'agency_id' => 1,
            'name' => 'Digital Rubix Core',
            'status' => 'active',
            'data_retention_months' => 24,
        ]);

        // 2. Team Members (RBAC Auth)
        TeamMember::create([
            'agency_id' => 1,
            'name' => 'Agency Admin',
            'email' => 'admin@digitalrubix.com',
            'password' => Hash::make('password'),
            'role' => 'Agency Admin',
            'assigned_clients' => [],
            'two_factor_enabled' => true,
            'two_factor_secret' => '123456',
        ]);

        TeamMember::create([
            'agency_id' => 1,
            'name' => 'Client Manager',
            'email' => 'manager@digitalrubix.com',
            'password' => Hash::make('password'),
            'role' => 'Client Manager',
            'assigned_clients' => [$sai->id, $bagnomy->id],
            'two_factor_enabled' => false,
        ]);

        TeamMember::create([
            'agency_id' => 1,
            'name' => 'Team Executive',
            'email' => 'exec@digitalrubix.com',
            'password' => Hash::make('password'),
            'role' => 'Team Executive',
            'assigned_clients' => [$sai->id],
            'two_factor_enabled' => false,
        ]);

        // 3. Platform Health Indicators
        PlatformHealth::create(['platform' => 'instagram', 'consecutive_failures' => 0, 'status' => 'healthy', 'last_checked_at' => now()]);
        PlatformHealth::create(['platform' => 'facebook', 'consecutive_failures' => 0, 'status' => 'healthy', 'last_checked_at' => now()]);
        PlatformHealth::create(['platform' => 'twitter', 'consecutive_failures' => 0, 'status' => 'healthy', 'last_checked_at' => now()]);
        PlatformHealth::create(['platform' => 'youtube', 'consecutive_failures' => 0, 'status' => 'healthy', 'last_checked_at' => now()]);

        // 4. Platform Connections
        PlatformConnection::create([
            'client_id' => $sai->id,
            'platform' => 'instagram',
            'access_token' => 'EAAG...test_token_ig',
            'platform_account_id' => '17841400000000',
            'health_status' => 'healthy',
            'token_expires_at' => Carbon::now()->addDays(45),
        ]);

        PlatformConnection::create([
            'client_id' => $sai->id,
            'platform' => 'facebook',
            'access_token' => 'EAAG...test_token_fb',
            'platform_account_id' => '100234567890123',
            'health_status' => 'healthy',
            'token_expires_at' => Carbon::now()->addDays(45),
        ]);

        // 5. Automation Rules with A/B Variants
        AutomationRule::create([
            'client_id' => $sai->id,
            'platform' => 'instagram',
            'trigger_type' => 'phone_or_buying_intent',
            'action_type' => 'reply_and_hide',
            'reply_template_variants' => [
                'Hi! We have sent full price details to your DM. Check your inbox!',
                'Bhai price details sent on DM! Let us know if you have any questions.',
            ],
            'business_hours_variant' => [
                'reply_text' => 'Thanks for reaching out! Our office is closed now, but our team will contact you first thing in the morning at 9 AM.'
            ],
            'is_active' => true,
        ]);

        // 6. Demo Leads across Pipeline Statuses
        Lead::create([
            'client_id' => $sai->id,
            'platform' => 'instagram',
            'source_comment_id' => 'comm_101',
            'contact_phone' => '+919876543210',
            'contact_name' => 'Rahul Sharma',
            'contact_handle' => 'rahul_s99',
            'status' => 'new',
            'score' => 'hot',
            'captured_at' => Carbon::now()->subMinutes(10),
            'notes' => 'Comment: "Bhai price batao, urgent buy karna hai. Call me +919876543210"',
        ]);

        Lead::create([
            'client_id' => $sai->id,
            'platform' => 'facebook',
            'source_comment_id' => 'comm_102',
            'contact_phone' => '+919123456789',
            'contact_name' => 'Priya Verma',
            'contact_handle' => 'priya_v',
            'status' => 'contacted',
            'score' => 'hot',
            'captured_at' => Carbon::now()->subHours(2),
            'notes' => 'Comment: "Please contact me on +919123456789 for business pricing."',
        ]);

        Lead::create([
            'client_id' => $bagnomy->id,
            'platform' => 'instagram',
            'source_comment_id' => 'comm_103',
            'contact_phone' => null,
            'contact_name' => 'Amit Patel',
            'contact_handle' => 'amit_p',
            'status' => 'qualified',
            'score' => 'warm',
            'captured_at' => Carbon::now()->subHours(5),
            'notes' => 'Comment: "Is this bag waterproof? Interested in buying."',
        ]);
    }
}
