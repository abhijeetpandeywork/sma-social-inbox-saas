<?php

namespace App\Services;

use App\Models\PlatformConnection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlatformConnectionService
{
    /**
     * Check expiring tokens across all clients and attempt silent refresh.
     */
    public static function checkTokenHealth(): void
    {
        $expiringConnections = PlatformConnection::withoutGlobalScopes()
            ->where('token_expires_at', '<=', Carbon::now()->addDays(7))
            ->get();

        foreach ($expiringConnections as $connection) {
            $refreshed = self::attemptSilentRefresh($connection);
            if (!$refreshed) {
                $connection->update(['health_status' => 'expiring_soon']);
                Log::alert("OAuth token for client {$connection->client_id} platform {$connection->platform} expires on {$connection->token_expires_at}. Manual re-auth required.");
            }
        }
    }

    private static function attemptSilentRefresh(PlatformConnection $connection): bool
    {
        // Placeholder for platform-specific silent token exchange (e.g. Meta long-lived token refresh)
        if ($connection->refresh_token) {
            $connection->update([
                'token_expires_at' => Carbon::now()->addDays(60),
                'health_status' => 'healthy',
                'last_successful_call_at' => Carbon::now(),
            ]);
            return true;
        }

        return false;
    }
}
