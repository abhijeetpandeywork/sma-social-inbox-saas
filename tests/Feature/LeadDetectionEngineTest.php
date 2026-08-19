<?php

namespace Tests\Feature;

use App\Services\LeadDetectionService;
use Tests\TestCase;

class LeadDetectionEngineTest extends TestCase
{
    public function test_phone_extraction_indian_and_international_formats()
    {
        $text1 = "Bhai rate batao please call me +919876543210";
        $this->assertEquals('+919876543210', LeadDetectionService::extractPhone($text1));

        $text2 = "Interested! My phone number is 9876543210 send details";
        $this->assertEquals('+919876543210', LeadDetectionService::extractPhone($text2));

        $text3 = "Call +14155552671 for pricing";
        $this->assertEquals('+14155552671', LeadDetectionService::extractPhone($text3));
    }

    public function test_intent_classification_hinglish_keywords()
    {
        $resHot = LeadDetectionService::classifyIntent("Bhai price kitna hai?", null);
        $this::assertTrue($resHot['is_lead']);
        $this::assertEquals('hot', $resHot['score']);

        $resWarm = LeadDetectionService::classifyIntent("I am interested in this product", null);
        $this::assertTrue($resWarm['is_lead']);
        $this::assertEquals('warm', $resWarm['score']);

        $resCold = LeadDetectionService::classifyIntent("Nice photo!", null);
        $this::assertFalse($resCold['is_lead']);
    }

    public function test_whatsapp_link_generation()
    {
        $link = LeadDetectionService::getWhatsAppLink('+919876543210');
        $this->assertEquals('https://wa.me/919876543210', $link);
    }
}
