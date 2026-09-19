<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_oidc_metadata_is_rate_limited_by_ip(): void
    {
        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->getJson('/.well-known/openid-configuration')->assertOk();
        }

        $this->getJson('/.well-known/openid-configuration')->assertTooManyRequests();
    }

    public function test_named_limiters_use_non_spoofable_request_identity(): void
    {
        $request = Request::create('/.well-known/jwks.json', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.10']);
        $this->assertSame('203.0.113.10', RateLimiter::limiter('oidc-public')($request)->key);
    }
}
