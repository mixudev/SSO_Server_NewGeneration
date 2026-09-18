<?php

namespace App\Domain\Applications\Services;

use App\Models\Identity\Organization;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class ApplicationWizardIntegrityValidator
{
    public function __construct(private RedirectUriValidator $redirectUriValidator) {}

    /**
     * @param  array<string, mixed>  $wizard
     * @return array<string, mixed>
     */
    public function validate(array $wizard): array
    {
        $basic = $this->arraySegment($wizard, 'basic');
        $protocol = $this->arraySegment($wizard, 'protocol');
        $redirectUris = $wizard['redirect_uris'] ?? null;
        $scopeIds = $wizard['scope_ids'] ?? null;
        $claims = $this->arraySegment($wizard, 'claims');
        $security = $this->arraySegment($wizard, 'security');

        $this->validateBasic($basic);
        $this->validateProtocol($protocol);
        $canonicalRedirectUris = $this->validateRedirectUris($redirectUris);
        $normalizedScopeIds = $this->validateScopes($scopeIds);
        $normalizedClaims = $this->validateClaims($claims);
        $this->validateSecurity($security, $protocol['client_type']);

        return [
            'basic' => $basic,
            'protocol' => $protocol,
            'redirect_uris' => $canonicalRedirectUris,
            'scope_ids' => $normalizedScopeIds,
            'claims' => $normalizedClaims,
            'security' => $security,
        ];
    }

    private function validateBasic(array $basic): void
    {
        if (! isset($basic['organization_id'], $basic['name'], $basic['slug'])
            || ! is_string($basic['organization_id'])
            || ! is_string($basic['name'])
            || ! is_string($basic['slug'])
            || $basic['name'] === ''
            || preg_match('/^[A-Za-z0-9_-]+$/', $basic['slug']) !== 1
            || ! Organization::query()
                ->whereKey($basic['organization_id'])
                ->where('status', 'active')
                ->exists()
        ) {
            throw ValidationException::withMessages(['wizard' => 'Application details are invalid.']);
        }
    }

    private function validateProtocol(array $protocol): void
    {
        if (! in_array($protocol['protocol_mode'] ?? null, ['oauth2', 'oidc'], true)
            || ! in_array($protocol['client_type'] ?? null, ['confidential_web', 'public_spa', 'native'], true)
        ) {
            throw ValidationException::withMessages(['wizard' => 'Protocol settings are invalid.']);
        }
    }

    /**
     * @return list<string>
     */
    private function validateRedirectUris(mixed $redirectUris): array
    {
        if (! is_array($redirectUris) || count($redirectUris) < 1 || count($redirectUris) > 20) {
            throw ValidationException::withMessages(['redirect_uris' => 'Redirect URIs are invalid.']);
        }

        $canonical = [];
        foreach ($redirectUris as $index => $uri) {
            if (! is_string($uri)) {
                throw ValidationException::withMessages(["redirect_uris.{$index}" => 'Redirect URI is invalid.']);
            }

            try {
                $canonical[] = $this->redirectUriValidator->canonicalize($uri);
            } catch (InvalidArgumentException) {
                throw ValidationException::withMessages(["redirect_uris.{$index}" => 'Redirect URI is invalid.']);
            }
        }

        if (count($canonical) !== count(array_unique($canonical))) {
            throw ValidationException::withMessages(['redirect_uris' => 'Redirect URIs must be unique.']);
        }

        return $canonical;
    }

    /**
     * @return list<string>
     */
    private function validateScopes(mixed $scopeIds): array
    {
        if (! is_array($scopeIds)) {
            throw ValidationException::withMessages(['scope_ids' => 'Scopes are invalid.']);
        }

        $scopeIds = array_values($scopeIds);
        if (array_filter($scopeIds, fn (mixed $id): bool => ! is_string($id) || $id === '') !== []) {
            throw ValidationException::withMessages(['scope_ids' => 'Scopes are invalid.']);
        }
        if (count($scopeIds) !== count(array_unique($scopeIds))) {
            throw ValidationException::withMessages(['scope_ids' => 'Scopes are invalid.']);
        }

        return $scopeIds;
    }

    /**
     * @param  array<string, mixed>  $claims
     * @return array{keys: list<string>, version: int}
     */
    private function validateClaims(array $claims): array
    {
        $keys = $claims['keys'] ?? null;
        $version = $claims['version'] ?? null;
        if (! is_array($keys) || ! is_int($version) || $version < 1) {
            throw ValidationException::withMessages(['claims' => 'Claim policy is invalid.']);
        }

        $keys = array_values($keys);
        if (array_filter($keys, fn (mixed $key): bool => ! is_string($key) || $key === '') !== []) {
            throw ValidationException::withMessages(['claims' => 'Claim policy is invalid.']);
        }
        if (count($keys) !== count(array_unique($keys))) {
            throw ValidationException::withMessages(['claims' => 'Claim policy is invalid.']);
        }

        return ['keys' => $keys, 'version' => $version];
    }

    /** @param array<string, mixed> $security */
    private function validateSecurity(array $security, string $clientType): void
    {
        $consentPolicy = $security['consent_policy'] ?? null;
        $maxAge = $security['session_max_age'] ?? null;
        $idleTimeout = $security['session_idle_timeout'] ?? null;
        if (! in_array($consentPolicy, ['explicit', 'implicit', 'none'], true)
            || ! is_int($maxAge) || $maxAge < 300 || $maxAge > 86400
            || ! is_int($idleTimeout) || $idleTimeout < 60 || $idleTimeout > 900
            || $idleTimeout > $maxAge
            || ($clientType === 'public_spa' && $idleTimeout > 600)
        ) {
            throw ValidationException::withMessages(['security' => 'Security policy is invalid.']);
        }
    }

    /** @return array<string, mixed> */
    private function arraySegment(array $wizard, string $key): array
    {
        if (! is_array($wizard[$key] ?? null)) {
            throw ValidationException::withMessages(['wizard' => 'Application wizard data is incomplete.']);
        }

        return $wizard[$key];
    }
}
