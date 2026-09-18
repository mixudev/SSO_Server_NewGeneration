<?php

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Enums\ClaimValueType;
use InvalidArgumentException;

final class ClaimPolicyEngine
{
    /**
     * @param  array{version: int, claims: list<array{key: string, source: string, scopes: list<string>, value_type: string}>}  $policy
     * @param  list<string>  $requestedScopes
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function resolve(array $policy, array $requestedScopes, array $attributes): array
    {
        $version = $policy['version'] ?? null;

        if (! is_int($version) || $version < 1) {
            throw new InvalidArgumentException('Claim policy version is invalid.');
        }

        $claims = [];
        $scopeSet = array_fill_keys($requestedScopes, true);

        foreach ($policy['claims'] ?? [] as $definition) {
            $requiredScopes = $definition['scopes'] ?? [];

            if (array_diff($requiredScopes, array_keys($scopeSet)) !== []) {
                continue;
            }

            $source = $definition['source'] ?? null;
            $key = $definition['key'] ?? null;
            $valueType = ClaimValueType::tryFrom($definition['value_type'] ?? '');

            if (! is_string($source) || ! is_string($key) || ! $valueType) {
                throw new InvalidArgumentException('Claim policy definition is invalid.');
            }

            if (array_key_exists($key, $claims)) {
                throw new InvalidArgumentException('Claim policy contains duplicate keys.');
            }

            if (! array_key_exists($source, $attributes)) {
                continue;
            }

            $value = $attributes[$source];

            if (! $this->matchesType($value, $valueType)) {
                throw new InvalidArgumentException('Claim value does not match policy type.');
            }

            $claims[$key] = $value;
        }

        return $claims;
    }

    private function matchesType(mixed $value, ClaimValueType $type): bool
    {
        return match ($type) {
            ClaimValueType::String => is_string($value),
            ClaimValueType::Boolean => is_bool($value),
            ClaimValueType::Array => is_array($value),
            ClaimValueType::Json => is_string($value) && json_validate($value),
        };
    }
}
