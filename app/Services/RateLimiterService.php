<?php

namespace App\Services;

use App\Models\RateLimitCounter;
use Carbon\Carbon;

class RateLimiterService
{
    /**
     * Check if client platform call limit is exceeded and increment counter.
     */
    public static function checkAndIncrement(int $clientId, string $platform, int $maxCallsPerWindow = 200, int $windowMinutes = 15): bool
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->subMinutes($now->minute % $windowMinutes)->second(0);

        $counter = RateLimitCounter::firstOrCreate(
            [
                'client_id' => $clientId,
                'platform' => $platform,
                'window_start' => $windowStart,
            ],
            [
                'call_count' => 0,
            ]
        );

        if ($counter->call_count >= $maxCallsPerWindow) {
            return false; // Rate limit exceeded
        }

        $counter->increment('call_count');
        return true;
    }
}
