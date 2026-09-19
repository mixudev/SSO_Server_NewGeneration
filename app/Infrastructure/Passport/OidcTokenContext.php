<?php

namespace App\Infrastructure\Passport;

use App\Models\OAuth\AuthorizationTransaction;

final class OidcTokenContext
{
    private ?AuthorizationTransaction $transaction = null;

    private ?string $authorizationCodeHash = null;

    public function set(?AuthorizationTransaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function setAuthorizationCodeHash(?string $hash): void
    {
        $this->authorizationCodeHash = $hash;
    }

    public function authorizationCodeHash(): ?string
    {
        return $this->authorizationCodeHash;
    }

    public function transaction(): ?AuthorizationTransaction
    {
        return $this->transaction;
    }
}
