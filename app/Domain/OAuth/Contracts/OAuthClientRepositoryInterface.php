<?php

namespace App\Domain\OAuth\Contracts;

use App\Domain\OAuth\Data\OAuthClientData;

interface OAuthClientRepositoryInterface
{
    /** @param list<string> $redirectUris */
    public function createAuthorizationCodeClient(string $name, array $redirectUris, bool $confidential): OAuthClientData;

    public function find(string|int $id): ?OAuthClientData;

    public function findActive(string|int $id): ?OAuthClientData;

    public function regenerateSecret(string|int $id): OAuthClientData;

    public function revoke(string|int $id): void;
}
