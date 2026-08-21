<?php

namespace App\Console\Commands;

use App\Models\ActionLog;
use App\Models\AutomationRule;
use App\Models\Client;
use App\Models\FailedAction;
use App\Models\Lead;
use App\Models\PiiAccessLog;
use App\Models\PlatformConnection;
use App\Models\PlatformHealth;
use App\Models\RateLimitCounter;
use App\Models\RawEvent;
use App\Models\SlaEscalation;
use App\Models\TeamMember;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ResetDemoDataCommand extends Command
{
    protected $signature = 'app:reset-demo-data {--clean-admin : Reset admin to default password and 2FA}';
    protected $description = 'Purge all mock leads, mock connections, and sample rules to leave a clean slate for manual setup';

    public function handle(): int
    {
        $this->info('Resetting Social Inbox database to clean slate...');

        Schema::disableForeignKeyConstraints();

        // 1. Wipe all transactional data & mock records
        Lead::truncate();
        AutomationRule::truncate();
        PlatformConnection::truncate();
        RawEvent::truncate();
        ActionLog::truncate();
        PiiAccessLog::truncate();
        FailedAction::truncate();
        SlaEscalation::truncate();
        RateLimitCounter::truncate();

        // 2. Clear non-admin team members
        TeamMember::where('role', '!=', 'Agency Admin')->delete();

        // 3. Clear mock clients
        Client::truncate();

        // 4. Reset platform health indicators to fresh 0 status
        PlatformHealth::truncate();
        foreach (['instagram', 'facebook', 'twitter', 'youtube', 'gmb', 'linkedin'] as $platform) {
            PlatformHealth::create([
                'platform' => $platform,
                'consecutive_failures' => 0,
                'status' => 'healthy',
                'circuit_state' => 'closed',
                'last_checked_at' => now(),
            ]);
        }

        // 5. Ensure primary Agency Admin exists
        $admin = TeamMember::where('role', 'Agency Admin')->first();
        if (!$admin || $this->option('clean-admin')) {
            if ($admin) {
                $admin->delete();
            }
            TeamMember::create([
                'agency_id' => 1,
                'name' => 'Abhijeet Pandey',
                'email' => 'abhijeet.digitalrubix@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'Agency Admin',
                'assigned_clients' => [],
                'two_factor_enabled' => true,
                'two_factor_secret' => '123456',
            ]);
            $this->info('Created/Reset primary Admin: abhijeet.digitalrubix@gmail.com (Password: password, 2FA: 123456)');
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Clean slate completed successfully! Ready for manual configuration.');
        return Command::SUCCESS;
    }
}
