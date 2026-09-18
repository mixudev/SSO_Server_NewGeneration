<?php

namespace App\Infrastructure\Passport;

use App\Models\Identity\ApplicationCredential;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Laravel\Passport\Passport;

final class ActiveClientRepository extends PassportClientRepository
{
    public function findActive(string|int $id): ?Client
    {
        $isAllowed = ApplicationCredential::query()
            ->where('passport_client_id', (string) $id)
            ->where('status', 'active')
            ->whereHas('application', function ($query): void {
                $query->where('status', 'active')
                    ->whereHas('organization', fn ($organization): mixed => $organization->where('status', 'active'));
            })
            ->exists();

        return $isAllowed
            ? Passport::client()->newQuery()->whereKey($id)->where('revoked', false)->first()
            : null;
    }
}
