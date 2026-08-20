<?php

namespace App\Services;

use App\Models\PlatformConnection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TokenHealthService
{
    /**
     * Check all platform connections for tokens expiring within 7 days.
     */
    public static function checkExpiringTokens(): int
    {
        $expiringConnections = PlatformConnection::withoutGlobalScopes()
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<=', Carbon::now()->addDays(7))
            ->get();

        foreach ($expiringConnections as $conn) {
            $daysLeft = Carbon::now()->diffInDays($conn->token_expires_at, false);
            if ($daysLeft <= 0) {
                $conn->update(['health_status' => 'expired']);
                Log::error("Platform token EXPIRED for client #{$conn->client_id} on {$conn->platform} (Account ID: {$conn->platform_account_id})");
            } else {
                $conn->update(['health_status' => 'expiring_soon']);
                Log::warning("Platform token expiring in {$daysLeft} days for client #{$conn->client_id} on {$conn->platform}");
            }
        }

        return $expiringConnections->count();
    }
}
