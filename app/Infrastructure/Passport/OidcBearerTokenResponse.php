<?php

namespace App\Infrastructure\Passport;

use App\Domain\Identity\Services\ClaimPolicyEngine;
use App\Domain\Oidc\Services\IdTokenBuilder;
use App\Models\User;
use Illuminate\Support\Carbon;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;

final class OidcBearerTokenResponse extends BearerTokenResponse
{
    protected function getExtraParams(AccessTokenEntityInterface $accessToken): array
    {
        $transaction = app(OidcAuthorizationTransactionResolver::class)->resolve($accessToken);

        if ($transaction === null) {
            return [];
        }

        if ($transaction->application?->protocol_mode !== 'oidc' || $transaction->nonce_hash === null) {
            return [];
        }

        $user = User::query()->find($accessToken->getUserIdentifier());
        if ($user === null) {
            return [];
        }

        $scopes = preg_split('/\s+/', trim($transaction->scope_string), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $claims = app(ClaimPolicyEngine::class)->resolve(
            $transaction->application->activeClaimPolicy?->rules_json ?? ['version' => 1, 'claims' => []],
            $scopes,
            [
                'user.name' => $user->name,
                'user.email' => $user->email,
                'user.email_verified' => $user->email_verified_at !== null,
            ],
        );

        $nonce = $this->recoverNonce($transaction);
        if ($nonce === null) {
            return [];
        }

        $issuedAt = Carbon::now()->timestamp;
        $idToken = app(IdTokenBuilder::class)->build(
            user: $user,
            clientId: $accessToken->getClient()->getIdentifier(),
            nonce: $nonce,
            issuedAt: $issuedAt,
            expiresAt: $issuedAt + 3600,
            claims: $claims,
            issuer: rtrim((string) config('app.url'), '/'),
        );

        $transaction->forceFill([
            'completed_at' => now(),
            'nonce_encrypted' => null,
        ])->save();

        return ['id_token' => $idToken];
    }

    private function recoverNonce(object $transaction): ?string
    {
        $encryptedNonce = $transaction->nonce_encrypted;

        if (! is_string($encryptedNonce)) {
            return null;
        }

        try {
            return decrypt($encryptedNonce);
        } catch (\Throwable) {
            return null;
        }
    }
}
