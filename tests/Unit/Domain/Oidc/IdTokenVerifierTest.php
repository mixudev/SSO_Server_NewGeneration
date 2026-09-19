<?php

namespace Tests\Unit\Domain\Oidc;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Domain\Oidc\Services\IdTokenBuilder;
use App\Domain\Oidc\Services\IdTokenVerifier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IdTokenVerifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifier_accepts_a_valid_token(): void
    {
        $key = app(KeyManagerInterface::class)->generate();
        $user = User::factory()->create();
        $token = app(IdTokenBuilder::class)->build($user, 'client-123', 'nonce-abc', 1_700_000_000, 1_700_003_600, [], 'https://sso.example.test');

        $claims = app(IdTokenVerifier::class)->verify($token, $key, 'https://sso.example.test', 'client-123', 'nonce-abc', 1_700_000_100);

        $this->assertSame((string) $user->uuid, $claims['sub']);
    }

    #[DataProvider('invalidTokenCases')]
    public function test_verifier_rejects_invalid_token(string $mutation): void
    {
        $key = app(KeyManagerInterface::class)->generate();
        $user = User::factory()->create();
        $builder = app(IdTokenBuilder::class);
        $token = $builder->build($user, 'client-123', 'nonce-abc', 1_700_000_000, 1_700_003_600, [], 'https://sso.example.test');

        if ($mutation === 'tamper') {
            $parts = explode('.', $token);
            $parts[1] = rtrim(strtr(base64_encode(json_encode(['iss' => 'https://evil.example'])), '+/', '-_'), '=');
            $token = implode('.', $parts);
        }

        if ($mutation === 'algorithm') {
            $parts = explode('.', $token);
            $parts[0] = rtrim(strtr(base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'none', 'kid' => $key->kid])), '+/', '-_'), '=');
            $token = implode('.', $parts);
        }

        $this->expectException(InvalidArgumentException::class);
        app(IdTokenVerifier::class)->verify(
            $token,
            $key,
            $mutation === 'issuer' ? 'https://other.example' : 'https://sso.example.test',
            $mutation === 'audience' ? 'other-client' : 'client-123',
            $mutation === 'nonce' ? 'other-nonce' : 'nonce-abc',
            $mutation === 'expired' ? 1_700_003_601 : 1_700_000_100,
        );
    }

    /** @return array<string, array{string}> */
    public static function invalidTokenCases(): array
    {
        return [
            'tampered payload' => ['tamper'],
            'algorithm confusion' => ['algorithm'],
            'issuer mismatch' => ['issuer'],
            'audience mismatch' => ['audience'],
            'nonce mismatch' => ['nonce'],
            'expired token' => ['expired'],
        ];
    }
}
