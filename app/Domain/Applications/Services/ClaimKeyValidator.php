<?php

namespace App\Domain\Applications\Services;

use InvalidArgumentException;

final class ClaimKeyValidator
{
    /**
     * @var list<string>
     */
    private const RESERVED_KEYS = [
        'iss',
        'sub',
        'aud',
        'exp',
        'iat',
        'auth_time',
        'nonce',
        'email',
        'name',
        'preferred_username',
        'phone_number',
        'roles',
        'organization_id',
    ];

    public function validate(string $key): string
    {
        if ($key !== trim($key)) {
            throw new InvalidArgumentException('Claim key is invalid.');
        }

        $normalizedKey = $key;

        if ($normalizedKey === '' || strlen($normalizedKey) > 128) {
            throw new InvalidArgumentException('Claim key is invalid.');
        }

        if (preg_match('/[\x00-\x20\x7f]/', $normalizedKey) === 1) {
            throw new InvalidArgumentException('Claim key is invalid.');
        }

        if (in_array($normalizedKey, self::RESERVED_KEYS, true)) {
            return $normalizedKey;
        }

        if (preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $normalizedKey) !== 1) {
            throw new InvalidArgumentException('Claim key is invalid.');
        }

        return $normalizedKey;
    }
}
