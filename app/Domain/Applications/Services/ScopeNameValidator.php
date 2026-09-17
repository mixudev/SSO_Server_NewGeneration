<?php

namespace App\Domain\Applications\Services;

use InvalidArgumentException;

final class ScopeNameValidator
{
    /**
     * @var list<string>
     */
    private const RESERVED_NAMES = [
        'openid',
        'profile',
        'email',
        'address',
        'phone',
    ];

    public function validate(string $name): string
    {
        $normalizedName = trim($name);

        if ($normalizedName === '' || strlen($normalizedName) > 128) {
            throw new InvalidArgumentException('Scope name is invalid.');
        }

        if (preg_match('/[\x00-\x20\x7f]/', $normalizedName) === 1) {
            throw new InvalidArgumentException('Scope name is invalid.');
        }

        if (in_array($normalizedName, self::RESERVED_NAMES, true)) {
            return $normalizedName;
        }

        if (preg_match('/^[a-z0-9]+(?::[a-z0-9][a-z0-9_-]*)+$/', $normalizedName) !== 1) {
            throw new InvalidArgumentException('Custom scope must be namespaced.');
        }

        return $normalizedName;
    }
}
