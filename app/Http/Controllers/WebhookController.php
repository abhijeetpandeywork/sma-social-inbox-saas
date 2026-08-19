<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookEventJob;
use App\Models\RawEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Meta Webhook Verification (GET)
     */
    public function verifyMeta(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = config('services.meta.verify_token', env('META_VERIFY_TOKEN', 'social_inbox_secret_token'));

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return response($challenge, 200);
        }

        return response()->json(['error' => 'Unauthorized verification token'], 403);
    }

    /**
     * Receive and Deduplicate Meta Webhook Payload (POST)
     */
    public function handleMeta(Request $request)
    {
        $rawPayload = $request->getContent();
        $signatureHeader = $request->header('X-Hub-Signature-256');
        $appSecret = config('services.meta.app_secret', env('META_APP_SECRET', 'test_app_secret'));

        // 1. Signature Verification (skip in local testing if signature header not set)
        if ($signatureHeader && !app()->environment('testing')) {
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $rawPayload, $appSecret);
            if (!hash_equals($expectedSignature, $signatureHeader)) {
                return response()->json(['error' => 'Invalid webhook signature'], 401);
            }
        }

        $data = $request->json()->all();
        if (empty($data)) {
            return response()->json(['status' => 'empty_payload'], 200);
        }

        // 2. Generate Event Hash & Deduplicate
        $platform = $data['object'] ?? 'meta';
        $entries = $data['entry'] ?? [];

        foreach ($entries as $entry) {
            $entryId = $entry['id'] ?? 'unknown';
            $time = $entry['time'] ?? time();
            $eventHash = hash('sha256', $platform . '_' . $entryId . '_' . $time . '_' . md5(json_encode($entry)));

            // Idempotency check: Reject duplicate webhook delivery
            if (RawEvent::where('event_hash', $eventHash)->exists()) {
                continue;
            }

            // 3. Store raw event & dispatch queue job
            $rawEvent = RawEvent::create([
                'platform' => $platform,
                'event_type' => $entry['changes'][0]['field'] ?? $data['object'] ?? 'webhook',
                'event_hash' => $eventHash,
                'payload_json' => $entry,
                'processed' => false,
            ]);

            ProcessWebhookEventJob::dispatch($rawEvent->id);
        }

        return response()->json(['status' => 'success', 'message' => 'Event received and queued'], 200);
    }
}
