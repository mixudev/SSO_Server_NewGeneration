<?php

namespace App\Domain\Identity\Services;

final class ClaimPolicyScopeMapper
{
    /** @return list<string> */
    public function requiredScopes(string $claimKey): array
    {
        return match ($claimKey) {
            'user.email', 'user.email_verified' => ['email'],
            'user.name' => ['profile'],
            default => [],
        };
    }
}
