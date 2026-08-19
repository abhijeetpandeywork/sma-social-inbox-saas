<?php

namespace App\Services;

use App\Models\PlatformHealth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CircuitBreakerService
{
    public const FAILURE_THRESHOLD = 10;

    /**
     * Check if a platform circuit is open ('held') or closed ('healthy').
     */
    public static function isAllowed(string $platform): bool
    {
        $health = PlatformHealth::firstOrCreate(
            ['platform' => strtolower($platform)],
            ['status' => 'healthy', 'consecutive_failures' => 0]
        );

        return $health->status === 'healthy';
    }

    /**
     * Report a successful call to reset consecutive failure count.
     */
    public static function reportSuccess(string $platform): void
    {
        $health = PlatformHealth::where('platform', strtolower($platform))->first();
        if ($health) {
            $health->update([
                'consecutive_failures' => 0,
                'status' => 'healthy',
                'last_checked_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * Report a failure and open circuit if threshold exceeded.
     */
    public static function reportFailure(string $platform): void
    {
        $health = PlatformHealth::firstOrCreate(
            ['platform' => strtolower($platform)],
            ['status' => 'healthy', 'consecutive_failures' => 0]
        );

        $failures = $health->consecutive_failures + 1;
        $status = $failures >= self::FAILURE_THRESHOLD ? 'held' : 'healthy';

        $health->update([
            'consecutive_failures' => $failures,
            'status' => $status,
            'last_checked_at' => Carbon::now(),
        ]);

        if ($status === 'held') {
            Log::warning("Circuit breaker OPEN (held) for platform: {$platform} after {$failures} consecutive failures.");
        }
    }
}
