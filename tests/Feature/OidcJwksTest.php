<?php

namespace Tests\Feature;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OidcJwksTest extends TestCase
{
    use RefreshDatabase;

    public function test_jwks_publishes_only_active_public_rsa_material(): void
    {
        $key = app(KeyManagerInterface::class)->generate();

        $this->getJson('/.well-known/jwks.json')
            ->assertOk()
            ->assertJsonPath('keys.0.kid', $key->kid)
            ->assertJsonPath('keys.0.kty', 'RSA')
            ->assertJsonPath('keys.0.alg', 'RS256')
            ->assertJsonMissingPath('keys.0.d')
            ->assertJsonMissingPath('keys.0.private_key');
    }
}
