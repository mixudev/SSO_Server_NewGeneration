<?php

namespace App\Http\Controllers\Oidc;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Http\Controllers\Controller;
use App\Models\Identity\SigningKey;
use Illuminate\Http\JsonResponse;
use RuntimeException;

final class JwksController extends Controller
{
    public function __construct(private KeyManagerInterface $keys) {}

    public function __invoke(): JsonResponse
    {
        $keys = $this->keys->verificationKeys()->map(
            fn (SigningKey $key): array => $this->jwk($key),
        )->values()->all();

        return response()->json(['keys' => $keys])
            ->header('Cache-Control', 'public, max-age=300, must-revalidate');
    }

    /** @return array<string, string> */
    private function jwk(SigningKey $key): array
    {
        $details = openssl_pkey_get_details(openssl_pkey_get_public($key->public_key));

        if ($details === false || ! isset($details['rsa']['n'], $details['rsa']['e'])) {
            throw new RuntimeException('Unable to read signing key.');
        }

        return [
            'kty' => 'RSA',
            'use' => 'sig',
            'kid' => $key->kid,
            'alg' => 'RS256',
            'n' => $this->base64Url($details['rsa']['n']),
            'e' => $this->base64Url($details['rsa']['e']),
        ];
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
