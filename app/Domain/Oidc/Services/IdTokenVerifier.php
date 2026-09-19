<?php

namespace App\Domain\Oidc\Services;

use App\Models\Identity\SigningKey;
use InvalidArgumentException;

final class IdTokenVerifier
{
    /** @return array<string, mixed> */
    public function verify(
        string $token,
        SigningKey $key,
        string $issuer,
        string $audience,
        string $nonce,
        int $now,
    ): array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new InvalidArgumentException('ID token format is invalid.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = $this->decode($encodedHeader);
        $payload = $this->decode($encodedPayload);
        $signature = $this->decodeBinary($encodedSignature);

        if (($header['alg'] ?? null) !== 'RS256' || ($header['kid'] ?? null) !== $key->kid) {
            throw new InvalidArgumentException('ID token signing algorithm or key is invalid.');
        }

        if (! is_string($payload['iss'] ?? null)
            || rtrim($payload['iss'], '/') !== rtrim($issuer, '/')
            || ! is_string($payload['sub'] ?? null)
            || ! is_string($payload['aud'] ?? null)
            || $payload['aud'] !== $audience
            || ! is_int($payload['iat'] ?? null)
            || ! is_int($payload['auth_time'] ?? null)
            || ! is_int($payload['exp'] ?? null)
            || ! is_string($payload['nonce'] ?? null)
            || ! hash_equals($nonce, $payload['nonce'])
            || $payload['iat'] > $now
            || $payload['exp'] <= $now
        ) {
            throw new InvalidArgumentException('ID token claims are invalid.');
        }

        $verified = openssl_verify($encodedHeader.'.'.$encodedPayload, $signature, $key->public_key, OPENSSL_ALGO_SHA256);
        if ($verified !== 1) {
            throw new InvalidArgumentException('ID token signature is invalid.');
        }

        return $payload;
    }

    /** @return array<string, mixed> */
    private function decode(string $value): array
    {
        $decoded = json_decode($this->decodeBinary($value), true);
        if (! is_array($decoded)) {
            throw new InvalidArgumentException('ID token JSON is invalid.');
        }

        return $decoded;
    }

    private function decodeBinary(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if ($decoded === false) {
            throw new InvalidArgumentException('ID token encoding is invalid.');
        }

        return $decoded;
    }
}
