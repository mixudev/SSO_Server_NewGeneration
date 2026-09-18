<?php

namespace App\Domain\Applications\Services;

use InvalidArgumentException;

final class ScopeAuthorizationEvaluator
{
    public function __construct(private ScopeSetNormalizer $scopeSetNormalizer) {}

    /**
     * @param  array<string, array{allowed: bool, status: string}>  $registeredScopes
     * @return list<string>
     */
    public function authorize(string $requestedScopeString, array $registeredScopes): array
    {
        $requestedScopes = $this->scopeSetNormalizer->normalize($requestedScopeString);
        $authorizedScopes = [];

        foreach ($requestedScopes as $scope) {
            $registration = $registeredScopes[$scope] ?? null;

            if ($registration === null
                || ! array_key_exists('allowed', $registration)
                || ! is_bool($registration['allowed'])
                || ! array_key_exists('status', $registration)
                || ! is_string($registration['status'])
                || ! $registration['allowed']
                || $registration['status'] !== 'active'
            ) {
                throw new InvalidArgumentException('Requested scope is not authorized.');
            }

            $authorizedScopes[] = $scope;
        }

        return $authorizedScopes;
    }
}
