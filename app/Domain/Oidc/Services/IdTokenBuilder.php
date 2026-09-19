<?php

namespace App\Domain\Oidc\Services;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Models\User;
use InvalidArgumentException;
use RuntimeException;

final class IdTokenBuilder
{
    public function __construct(private KeyManagerInterface $keys) {}

    /**
     * @param  array<string, mixed>  $claims
     */
    public function build(
        User $user,
        string $clientId,
        string $nonce,
        int $issuedAt,
        int $expiresAt,
        array $claims,
        string $issuer,
    ): string {
        if ($clientId === '' || $nonce === '' || $issuer === '' || $expiresAt <= $issuedAt) {
            throw new InvalidArgumentException('ID token inputs are invalid.');
        }

        if (array_intersect(array_keys($claims), ['iss', 'sub', 'aud', 'iat', 'exp', 'auth_time', 'nonce']) !== []) {
            throw new InvalidArgumentException('ID token claims contain reserved keys.');
        }

        if (filter_var($issuer, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('ID token issuer is invalid.');
        }

        $key = $this->keys->active();
        $header = ['typ' => 'JWT', 'alg' => 'RS256', 'kid' => $key->kid];
        $payload = [
            'iss' => rtrim($issuer, '/'),
            'sub' => (string) $user->uuid,
            'aud' => $clientId,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'auth_time' => $issuedAt,
            'nonce' => $nonce,
            ...$claims,
        ];
        $encoded = $this->base64Url(json_encode($header, JSON_THROW_ON_ERROR)).'.'.$this->base64Url(json_encode($payload, JSON_THROW_ON_ERROR));

        if (! openssl_sign($encoded, $signature, $key->private_key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign ID token.');
        }

        return $encoded.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
