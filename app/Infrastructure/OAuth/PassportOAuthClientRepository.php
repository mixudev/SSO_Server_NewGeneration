<?php

namespace App\Infrastructure\OAuth;

use App\Domain\OAuth\Contracts\OAuthClientRepositoryInterface;
use App\Domain\OAuth\Data\OAuthClientData;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

final class PassportOAuthClientRepository implements OAuthClientRepositoryInterface
{
    public function __construct(private ClientRepository $clients) {}

    public function createAuthorizationCodeClient(string $name, array $redirectUris, bool $confidential): OAuthClientData
    {
        return $this->map($this->clients->createAuthorizationCodeGrantClient($name, $redirectUris, $confidential));
    }

    public function find(string|int $id): ?OAuthClientData
    {
        return $this->mapOptional($this->clients->find($id));
    }

    public function findActive(string|int $id): ?OAuthClientData
    {
        $client = method_exists($this->clients, 'findActive')
            ? $this->clients->findActive($id)
            : $this->clients->find($id);

        return $this->mapOptional($client);
    }

    public function regenerateSecret(string|int $id): OAuthClientData
    {
        $client = $this->clients->find($id);

        if ($client === null) {
            throw new \RuntimeException('OAuth client was not found.');
        }

        $this->clients->regenerateSecret($client);

        return $this->map($client);
    }

    public function revoke(string|int $id): void
    {
        $client = $this->clients->find($id);

        if ($client !== null && ! $client->revoked) {
            $this->clients->delete($client);
        }
    }

    private function mapOptional(?Client $client): ?OAuthClientData
    {
        return $client === null ? null : $this->map($client);
    }

    private function map(Client $client): OAuthClientData
    {
        return new OAuthClientData(
            clientId: (string) $client->getKey(),
            clientSecret: $client->confidential() ? $client->plainSecret : null,
            confidential: $client->confidential(),
            revoked: (bool) $client->revoked,
        );
    }
}
