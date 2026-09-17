<?php

namespace App\Domain\Applications\Services;

final class ScopeSetNormalizer
{
    public function __construct(private ScopeNameValidator $scopeNameValidator) {}

    /**
     * @return list<string>
     */
    public function normalize(string $scopeString): array
    {
        $scopes = preg_split('/\s+/', trim($scopeString), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $normalizedScopes = [];

        foreach ($scopes as $scope) {
            $normalizedScopes[] = $this->scopeNameValidator->validate($scope);
        }

        $normalizedScopes = array_values(array_unique($normalizedScopes));
        sort($normalizedScopes, SORT_STRING);

        return $normalizedScopes;
    }
}
