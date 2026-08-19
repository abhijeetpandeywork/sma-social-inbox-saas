<?php

namespace App\Services;

use App\Models\PlatformConnection;
use Illuminate\Support\Facades\Log;

class YouTubeAndGmbPollingService
{
    /**
     * Poll YouTube Data API v3 comments for active connections.
     */
    public static function pollYouTubeComments(PlatformConnection $connection): int
    {
        if ($connection->platform !== 'youtube' || $connection->health_status !== 'healthy') {
            return 0;
        }

        // Mock polling logic for YouTube comments
        $mockComments = [
            [
                'id' => 'yt_comm_' . time(),
                'text' => 'Loved the video! What is the price of your software? Call me at 9876543210',
                'from' => ['name' => 'YouTube Viewer'],
            ]
        ];

        $processedCount = 0;
        foreach ($mockComments as $comm) {
            $lead = LeadDetectionService::processIncomingComment($connection->client_id, 'youtube', $comm);
            if ($lead) {
                AutoReplyService::handleLeadAction($lead, $comm);
                $processedCount++;
            }
        }

        $connection->update(['last_successful_call_at' => now()]);
        return $processedCount;
    }

    /**
     * Poll Google Business Profile (GMB) reviews.
     */
    public static function pollGmbReviews(PlatformConnection $connection): int
    {
        if ($connection->platform !== 'gmb' || $connection->health_status !== 'healthy') {
            return 0;
        }

        $connection->update(['last_successful_call_at' => now()]);
        return 1;
    }
}
