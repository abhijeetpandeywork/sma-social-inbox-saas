<?php

namespace App\Services;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeadDetectionService
{
    /**
     * Extract phone number using Indian & International regex patterns.
     */
    public static function extractPhone(string $text): ?string
    {
        // 1. Matches +91 or 0 prefixed 10-digit Indian numbers with spaces/dashes
        if (preg_match('/(?:\+91[\s-]?)?[6-9]\d{9}/', $text, $matches)) {
            $clean = preg_replace('/\D/', '', $matches[0]);
            if (strlen($clean) === 10) {
                return '+91' . $clean;
            }
            if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
                return '+' . $clean;
            }
        }

        // 2. International E.164 pattern fallback
        if (preg_match('/\+?[1-9]\d{1,14}/', $text, $matches)) {
            $clean = preg_replace('/\D/', '', $matches[0]);
            if (strlen($clean) >= 10 && strlen($clean) <= 15) {
                return '+' . $clean;
            }
        }

        return null;
    }

    /**
     * Classify buying intent (Hot / Warm / Cold) via Claude API or fallback rules.
     */
    public static function classifyIntent(string $text, ?string $extractedPhone): array
    {
        $apiKey = env('ANTHROPIC_API_KEY');

        // If phone number exists, lead is automatically Hot
        if ($extractedPhone) {
            return [
                'is_lead' => true,
                'score' => 'hot',
                'reason' => 'Phone number provided in comment',
            ];
        }

        // Try Claude API if key present
        if ($apiKey) {
            try {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 100,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Analyze this social media comment for buying intent (Hinglish/English): \"{$text}\". Reply with JSON only: {\"is_lead\": true/false, \"score\": \"hot\"/\"warm\"/\"cold\"}"
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $content = $response->json('content.0.text', '');
                    if (preg_match('/\{.*\}/s', $content, $jsonMatch)) {
                        $parsed = json_decode($jsonMatch[0], true);
                        if (isset($parsed['is_lead'], $parsed['score'])) {
                            return $parsed;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Claude API classification failed, using fallback rule engine: " . $e->getMessage());
            }
        }

        // Fallback Hinglish Rule Engine
        $textLower = strtolower($text);
        $hotKeywords = ['price', 'bhai price', 'rate', 'cost', 'kitna hai', 'buy', 'purchase', 'call me', 'contact me', 'whatsapp me'];
        $warmKeywords = ['interested', 'details', 'dm', 'location', 'address', 'shop', 'kaha hai', 'info'];

        foreach ($hotKeywords as $kw) {
            if (str_contains($textLower, $kw)) {
                return ['is_lead' => true, 'score' => 'hot', 'reason' => "Matched intent keyword: {$kw}"];
            }
        }

        foreach ($warmKeywords as $kw) {
            if (str_contains($textLower, $kw)) {
                return ['is_lead' => true, 'score' => 'warm', 'reason' => "Matched intent keyword: {$kw}"];
            }
        }

        return ['is_lead' => false, 'score' => 'cold', 'reason' => 'No buying intent detected'];
    }

    /**
     * Process comment and create / deduplicate lead record.
     */
    public static function processIncomingComment(int $clientId, string $platform, array $commentData): ?Lead
    {
        $text = $commentData['text'] ?? '';
        $commentId = $commentData['id'] ?? null;
        $postId = $commentData['media']['id'] ?? $commentData['post_id'] ?? null;
        $authorName = $commentData['from']['name'] ?? $commentData['from']['username'] ?? 'Social User';
        $authorHandle = $commentData['from']['username'] ?? null;

        $phone = self::extractPhone($text);
        $intent = self::classifyIntent($text, $phone);

        if (!$intent['is_lead']) {
            return null;
        }

        // Check cross-platform deduplication by phone
        $parentLeadId = null;
        if ($phone) {
            $existingLead = Lead::withoutGlobalScopes()
                ->where('client_id', $clientId)
                ->where('contact_phone', $phone)
                ->first();

            if ($existingLead) {
                $parentLeadId = $existingLead->id;
            }
        }

        return Lead::withoutGlobalScopes()->create([
            'client_id' => $clientId,
            'platform' => $platform,
            'source_comment_id' => $commentId,
            'contact_phone' => $phone,
            'contact_name' => $authorName,
            'contact_handle' => $authorHandle,
            'status' => 'new',
            'score' => $intent['score'],
            'source_post_id' => $postId,
            'duplicate_of_lead_id' => $parentLeadId,
            'captured_at' => Carbon::now(),
            'notes' => "Comment: \"{$text}\" | " . ($intent['reason'] ?? ''),
        ]);
    }

    /**
     * Generate WhatsApp Deep-Link URL
     */
    public static function getWhatsAppLink(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }
        $clean = preg_replace('/\D/', '', $phone);
        return "https://wa.me/{$clean}";
    }
}
