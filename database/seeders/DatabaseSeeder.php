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
        $sai = Client::firstOrCreate(
            ['name' => 'Sai Business Solutions'],
            ['agency_id' => 1, 'status' => 'active', 'data_retention_months' => 12]
        );

        $bagnomy = Client::firstOrCreate(
            ['name' => 'Bagnomy'],
            ['agency_id' => 1, 'status' => 'active', 'data_retention_months' => 12]
        );

        $rubix = Client::firstOrCreate(
            ['name' => 'Digital Rubix Core'],
            ['agency_id' => 1, 'status' => 'active', 'data_retention_months' => 24]
        );

        // 2. Team Members (RBAC Auth)
        TeamMember::firstOrCreate(
            ['email' => 'admin@digitalrubix.com'],
            [
                'agency_id' => 1,
                'name' => 'Agency Admin',
                'password' => Hash::make('password'),
                'role' => 'Agency Admin',
                'assigned_clients' => [],
                'two_factor_enabled' => true,
                'two_factor_secret' => '123456',
            ]
        );

        TeamMember::firstOrCreate(
            ['email' => 'manager@digitalrubix.com'],
            [
                'agency_id' => 1,
                'name' => 'Client Manager',
                'password' => Hash::make('password'),
                'role' => 'Client Manager',
                'assigned_clients' => [$sai->id, $bagnomy->id],
                'two_factor_enabled' => false,
            ]
        );

        TeamMember::firstOrCreate(
            ['email' => 'exec@digitalrubix.com'],
            [
                'agency_id' => 1,
                'name' => 'Team Executive',
                'password' => Hash::make('password'),
                'role' => 'Team Executive',
                'assigned_clients' => [$sai->id],
                'two_factor_enabled' => false,
            ]
        );

        // 3. Platform Health Indicators
        foreach (['instagram', 'facebook', 'twitter', 'youtube'] as $p) {
            PlatformHealth::firstOrCreate(
                ['platform' => $p],
                ['consecutive_failures' => 0, 'status' => 'healthy', 'last_checked_at' => now()]
            );
        }

        // 4. Platform Connections
        PlatformConnection::firstOrCreate(
            ['client_id' => $sai->id, 'platform' => 'instagram'],
            [
                'access_token' => 'EAAG...test_token_ig',
                'platform_account_id' => '17841400000000',
                'health_status' => 'healthy',
                'token_expires_at' => Carbon::now()->addDays(45),
            ]
        );

        PlatformConnection::firstOrCreate(
            ['client_id' => $sai->id, 'platform' => 'facebook'],
            [
                'access_token' => 'EAAG...test_token_fb',
                'platform_account_id' => '100234567890123',
                'health_status' => 'healthy',
                'token_expires_at' => Carbon::now()->addDays(45),
            ]
        );

        // 5. Automation Rules with A/B Variants
        AutomationRule::firstOrCreate(
            ['client_id' => $sai->id, 'platform' => 'instagram'],
            [
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
            ]
        );

        // 6. Demo Leads across Pipeline Statuses
        Lead::firstOrCreate(
            ['source_comment_id' => 'comm_101'],
            [
                'client_id' => $sai->id,
                'platform' => 'instagram',
                'contact_phone' => '+919876543210',
                'contact_name' => 'Rahul Sharma',
                'contact_handle' => 'rahul_s99',
                'status' => 'new',
                'score' => 'hot',
                'captured_at' => Carbon::now()->subMinutes(10),
                'notes' => 'Comment: "Bhai price batao, urgent buy karna hai. Call me +919876543210"',
            ]
        );

        Lead::firstOrCreate(
            ['source_comment_id' => 'comm_102'],
            [
                'client_id' => $sai->id,
                'platform' => 'facebook',
                'contact_phone' => '+919123456789',
                'contact_name' => 'Priya Verma',
                'contact_handle' => 'priya_v',
                'status' => 'contacted',
                'score' => 'hot',
                'captured_at' => Carbon::now()->subHours(2),
                'notes' => 'Comment: "Please contact me on +919123456789 for business pricing."',
            ]
        );
    }
}
