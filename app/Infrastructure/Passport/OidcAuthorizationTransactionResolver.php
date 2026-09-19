<?php

namespace App\Infrastructure\Passport;

use App\Models\OAuth\AuthorizationTransaction;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

final class OidcAuthorizationTransactionResolver
{
    public function __construct(private OidcTokenContext $context) {}

    public function resolve(AccessTokenEntityInterface $accessToken): ?AuthorizationTransaction
    {
        $codeHash = $this->context->authorizationCodeHash();

        if ($codeHash === null) {
            return null;
        }

        return AuthorizationTransaction::query()
            ->with('application')
            ->where('authorization_code_hash', $codeHash)
            ->where('status', 'approved')
            ->whereNull('completed_at')
            ->first();
    }
}
