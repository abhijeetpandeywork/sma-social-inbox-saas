<?php

use App\Jobs\CheckSlaEscalationsJob;
use App\Services\TokenHealthService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Cron-Triggered Queue Processing (Hostinger Shared Hosting Compatible - PRD Section 4.1)
 * Runs every 1 minute with database cache lock to prevent overlapping runs.
 */
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping(10);

/**
 * SLA Escalations Check (Every 5 minutes - PRD Section 6)
 */
Schedule::job(new CheckSlaEscalationsJob)
    ->everyFiveMinutes()
    ->withoutOverlapping(5);

/**
 * Daily Platform Token Expiry Check & Alerting (PRD Section 4.6)
 */
Schedule::call(function () {
    TokenHealthService::checkExpiringTokens();
})->dailyAt('06:00');

/**
 * Daily Offsite Database Backup & Disaster Recovery (PRD Section 4.8)
 */
Schedule::command('app:backup-database')
    ->dailyAt('02:00');
