<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PlatformHealth;
use App\Models\RateLimitCounter;
use App\Services\CircuitBreakerService;
use App\Services\RateLimiterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimiterCircuitBreakerTest extends TestCase
{
    use RefreshDatabase;

    public function test_db_rate_limiter_increments_and_enforces_limit()
    {
        $client = Client::create(['name' => 'Rate Limit Client']);

        // Max 2 calls per window for testing
        $allowed1 = RateLimiterService::checkAndIncrement($client->id, 'instagram', 2, 15);
        $allowed2 = RateLimiterService::checkAndIncrement($client->id, 'instagram', 2, 15);
        $allowed3 = RateLimiterService::checkAndIncrement($client->id, 'instagram', 2, 15);

        $this->assertTrue($allowed1);
        $this->assertTrue($allowed2);
        $this->assertFalse($allowed3); // 3rd call rejected
        $this->assertEquals(2, RateLimitCounter::first()->call_count);
    }

    public function test_circuit_breaker_transitions_to_held_after_consecutive_failures()
    {
        $platform = 'instagram';

        for ($i = 0; $i < 9; $i++) {
            CircuitBreakerService::reportFailure($platform);
            $this->assertTrue(CircuitBreakerService::isAllowed($platform));
        }

        // 10th failure opens circuit (held)
        CircuitBreakerService::reportFailure($platform);
        $this->assertFalse(CircuitBreakerService::isAllowed($platform));
        $this->assertEquals('held', PlatformHealth::where('platform', $platform)->first()->status);

        // Success resets circuit to healthy
        CircuitBreakerService::reportSuccess($platform);
        $this->assertTrue(CircuitBreakerService::isAllowed($platform));
    }
}
