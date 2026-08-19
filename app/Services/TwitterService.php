<?php

namespace App\Services;

use App\Models\ActionLog;
use Carbon\Carbon;

class TwitterService
{
    // PRD Section 9 pricing structure for Twitter/X
    public const COST_READ = 0.005;      // $0.005 per read call
    public const COST_WRITE_DM = 0.015;   // $0.015 per write-DM
    public const COST_LINK_POST = 0.20;   // $0.20 per link-containing post

    /**
     * Record Twitter API usage and cost in action_log.
     */
    public static function recordUsage(int $clientId, string $actionType, string $targetId = null): float
    {
        $cost = match ($actionType) {
            'read_tweet', 'read_dm' => self::COST_READ,
            'send_dm', 'reply_tweet' => self::COST_WRITE_DM,
            'post_link' => self::COST_LINK_POST,
            default => self::COST_READ,
        };

        ActionLog::create([
            'client_id' => $clientId,
            'platform' => 'twitter',
            'action_type' => $actionType,
            'target_id' => $targetId,
            'status' => 'success',
            'error_message' => "Billed Usage Cost: \${$cost}",
            'attempt_count' => 1,
            'created_at' => Carbon::now(),
        ]);

        return $cost;
    }
}
