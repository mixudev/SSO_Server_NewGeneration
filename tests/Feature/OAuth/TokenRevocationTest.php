<?php

namespace Tests\Feature\OAuth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TokenRevocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_revocation_endpoint_is_idempotent_for_an_invalid_token(): void
    {
        $this->postJson('/oauth/revoke', [
            'token' => 'not-a-token',
            'token_type_hint' => 'access_token',
        ])->assertOk()->assertJson(['revoked' => true]);
    }

    public function test_unverified_jti_cannot_revoke_a_stored_token(): void
    {
        $tokenId = 'known-token-id';
        Passport::token()->newQuery()->forceCreate([
            'id' => $tokenId,
            'user_id' => 'user-id',
            'client_id' => 'client-id',
            'name' => null,
            'scopes' => [],
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $payload = rtrim(strtr(base64_encode(json_encode(['jti' => $tokenId])), '+/', '-_'), '=');
        $forgedToken = 'eyJhbGciOiJub25lIn0.'.$payload.'.forged';

        $this->postJson('/oauth/revoke', ['token' => $forgedToken])->assertOk();

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $tokenId,
            'revoked' => false,
        ]);
    }

    public function test_revocation_does_not_disclose_token_values(): void
    {
        $response = $this->postJson('/oauth/revoke', ['token' => 'not-a-token']);

        $response->assertOk()->assertJsonMissing(['token' => 'not-a-token']);
    }
}
