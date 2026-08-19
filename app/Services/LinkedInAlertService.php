<?php

namespace App\Services;

use App\Models\LinkedinAlert;

class LinkedInAlertService
{
    /**
     * Create notification-only LinkedIn alert.
     */
    public static function createAlert(int $clientId, string $alertType, ?string $sourceUrl): LinkedinAlert
    {
        return LinkedinAlert::withoutGlobalScopes()->create([
            'client_id' => $clientId,
            'alert_type' => $alertType,
            'source_url' => $sourceUrl,
            'status' => 'unread',
        ]);
    }
}
