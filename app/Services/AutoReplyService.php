<?php

namespace App\Services;

use App\Models\ActionLog;
use App\Models\AutomationRule;
use App\Models\Lead;
use App\Models\PlatformConnection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoReplyService
{
    /**
     * Trigger auto-reply and auto-hide logic for a captured lead.
     */
    public static function handleLeadAction(Lead $lead, array $commentData): void
    {
        $rule = AutomationRule::withoutGlobalScopes()
            ->where('client_id', $lead->client_id)
            ->where('platform', $lead->platform)
            ->where('is_active', true)
            ->first();

        $replyMessage = self::selectReplyTemplate($rule, $lead);

        // 1. Send Auto-Reply
        if ($replyMessage && $lead->source_comment_id) {
            self::sendReply($lead, $replyMessage);
        }

        // 2. Execute Auto-Hide Comment
        if ($lead->contact_phone || ($rule && $rule->action_type === 'reply_and_hide')) {
            self::hideComment($lead);
        }
    }

    /**
     * Select reply variant based on business hours and A/B testing array.
     */
    private static function selectReplyTemplate(?AutomationRule $rule, Lead $lead): string
    {
        if (!$rule) {
            return "Thank you for reaching out! Our team will contact you shortly.";
        }

        $now = Carbon::now();
        $isBusinessHours = $now->hour >= 9 && $now->hour < 19; // 9 AM - 7 PM

        if (!$isBusinessHours && !empty($rule->business_hours_variant)) {
            $offHours = $rule->business_hours_variant;
            return is_array($offHours) ? ($offHours['reply_text'] ?? $offHours[0] ?? '') : (string) $offHours;
        }

        $variants = $rule->reply_template_variants ?? [];
        if (is_array($variants) && count($variants) > 0) {
            return $variants[array_rand($variants)];
        }

        return "Thank you for your inquiry! We've received your request.";
    }

    /**
     * Send comment reply via platform Graph API
     */
    private static function sendReply(Lead $lead, string $message): void
    {
        if (!CircuitBreakerService::isAllowed($lead->platform)) {
            Log::warning("Skipped auto-reply for lead #{$lead->id}: Platform circuit is held.");
            return;
        }

        if (!RateLimiterService::checkAndIncrement($lead->client_id, $lead->platform)) {
            Log::warning("Skipped auto-reply for lead #{$lead->id}: Rate limit budget exceeded.");
            return;
        }

        $connection = PlatformConnection::withoutGlobalScopes()
            ->where('client_id', $lead->client_id)
            ->where('platform', $lead->platform)
            ->first();

        $status = 'success';
        $errorMessage = null;

        // Perform live API call if token is available
        if ($connection && !empty($connection->access_token) && !str_contains($connection->access_token, 'test_token')) {
            try {
                $response = Http::timeout(10)->post("https://graph.facebook.com/v19.0/{$lead->source_comment_id}/replies", [
                    'message' => $message,
                    'access_token' => $connection->access_token,
                ]);

                if ($response->successful()) {
                    $connection->update([
                        'last_successful_call_at' => now(),
                        'health_status' => 'healthy',
                    ]);
                    CircuitBreakerService::reportSuccess($lead->platform);
                } else {
                    $status = 'failed';
                    $errorMessage = $response->body();
                    CircuitBreakerService::reportFailure($lead->platform);
                }
            } catch (\Throwable $e) {
                $status = 'failed';
                $errorMessage = $e->getMessage();
                CircuitBreakerService::reportFailure($lead->platform);
            }
        } else {
            CircuitBreakerService::reportSuccess($lead->platform);
        }

        // Action Log record (append-only)
        ActionLog::create([
            'client_id' => $lead->client_id,
            'platform' => $lead->platform,
            'action_type' => 'auto_reply',
            'target_id' => (string) $lead->source_comment_id,
            'status' => $status,
            'error_message' => $errorMessage,
            'attempt_count' => 1,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Hide comment via platform Graph API
     */
    private static function hideComment(Lead $lead): void
    {
        if (!$lead->source_comment_id) {
            return;
        }

        if (!CircuitBreakerService::isAllowed($lead->platform)) {
            return;
        }

        if (!RateLimiterService::checkAndIncrement($lead->client_id, $lead->platform)) {
            return;
        }

        $connection = PlatformConnection::withoutGlobalScopes()
            ->where('client_id', $lead->client_id)
            ->where('platform', $lead->platform)
            ->first();

        $status = 'success';
        $errorMessage = null;

        // Perform live API call if token is available
        if ($connection && !empty($connection->access_token) && !str_contains($connection->access_token, 'test_token')) {
            try {
                $response = Http::timeout(10)->post("https://graph.facebook.com/v19.0/{$lead->source_comment_id}", [
                    'hide' => true,
                    'access_token' => $connection->access_token,
                ]);

                if ($response->successful()) {
                    $connection->update([
                        'last_successful_call_at' => now(),
                        'health_status' => 'healthy',
                    ]);
                    CircuitBreakerService::reportSuccess($lead->platform);
                } else {
                    $status = 'failed';
                    $errorMessage = $response->body();
                    CircuitBreakerService::reportFailure($lead->platform);
                }
            } catch (\Throwable $e) {
                $status = 'failed';
                $errorMessage = $e->getMessage();
                CircuitBreakerService::reportFailure($lead->platform);
            }
        } else {
            CircuitBreakerService::reportSuccess($lead->platform);
        }

        // Append-only audit log
        ActionLog::create([
            'client_id' => $lead->client_id,
            'platform' => $lead->platform,
            'action_type' => 'auto_hide',
            'target_id' => (string) $lead->source_comment_id,
            'status' => $status,
            'error_message' => $errorMessage,
            'attempt_count' => 1,
            'created_at' => Carbon::now(),
        ]);
    }
}
