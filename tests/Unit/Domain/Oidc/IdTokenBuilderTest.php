<?php

namespace Tests\Unit\Domain\Oidc;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Domain\Oidc\Services\IdTokenBuilder;
use App\Models\Identity\SigningKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdTokenBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_creates_rs256_token_with_validated_oidc_claims_and_nonce(): void
    {
        $key = $this->signingKey();
        $user = User::factory()->create();
        $builder = app(IdTokenBuilder::class);

        $token = $builder->build(
            user: $user,
            clientId: 'client-123',
            nonce: 'nonce-abc',
            issuedAt: 1_700_000_000,
            expiresAt: 1_700_003_600,
            claims: ['name' => $user->name],
            issuer: 'https://sso.example.test',
        );

        [$encodedHeader, $encodedPayload, $encodedSignature] = explode('.', $token);
        $header = json_decode(base64_decode(strtr($encodedHeader, '-_', '+/')), true, 512, JSON_THROW_ON_ERROR);
        $payload = json_decode(base64_decode(strtr($encodedPayload, '-_', '+/')), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('RS256', $header['alg']);
        $this->assertSame($key->kid, $header['kid']);
        $this->assertSame('https://sso.example.test', $payload['iss']);
        $this->assertSame((string) $user->uuid, $payload['sub']);
        $this->assertSame('client-123', $payload['aud']);
        $this->assertSame('nonce-abc', $payload['nonce']);
        $this->assertSame(1_700_003_600, $payload['exp']);
        $this->assertSame(1_700_000_000, $payload['auth_time']);
        $this->assertNotEmpty($encodedSignature);
    }

    public function test_builder_rejects_invalid_oidc_inputs(): void
    {
        $this->signingKey();
        $user = User::factory()->create();
        $builder = app(IdTokenBuilder::class);

        $this->expectException(\InvalidArgumentException::class);
        $builder->build($user, 'client-123', '', 100, 99, [], 'https://sso.example.test');
    }

    private function signingKey(): SigningKey
    {
        return app(KeyManagerInterface::class)->generate();
    }
}
