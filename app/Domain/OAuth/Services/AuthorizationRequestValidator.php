<?php

namespace App\Domain\OAuth\Services;

use App\Domain\Applications\Services\RedirectUriValidator;
use App\Domain\Applications\Services\ScopeAuthorizationEvaluator;
use App\Domain\OAuth\Data\AuthorizationRequestData;
use App\Domain\OAuth\Data\OAuthClientData;
use InvalidArgumentException;

final class AuthorizationRequestValidator
{
    public function __construct(
        private RedirectUriValidator $redirectUriValidator,
        private ScopeAuthorizationEvaluator $scopeAuthorizationEvaluator,
    ) {}

    /**
     * @param  list<string>  $registeredRedirectUris
     * @param  array<string, array{allowed: bool, status: string}>  $registeredScopes
     * @return list<string>
     */
    public function validate(
        AuthorizationRequestData $request,
        OAuthClientData $client,
        bool $applicationActive,
        bool $organizationActive,
        array $registeredRedirectUris,
        array $registeredScopes,
    ): array {
        if ($request->clientId !== $client->clientId || $client->revoked || ! $applicationActive || ! $organizationActive) {
            throw new InvalidArgumentException('Authorization client is unavailable.');
        }

        if ($request->responseType !== 'code') {
            throw new InvalidArgumentException('Only the code response type is supported.');
        }

        if ($request->state === '' || strlen($request->state) > 2048) {
            throw new InvalidArgumentException('Authorization state is invalid.');
        }

        if ($request->codeChallengeMethod !== 'S256'
            || preg_match('/^[A-Za-z0-9_-]{43,128}$/', $request->codeChallenge) !== 1
        ) {
            throw new InvalidArgumentException('PKCE S256 is required.');
        }

        $redirectMatches = false;

        foreach ($registeredRedirectUris as $registeredRedirectUri) {
            if ($this->redirectUriValidator->matches($registeredRedirectUri, $request->redirectUri)) {
                $redirectMatches = true;
                break;
            }
        }

        if (! $redirectMatches) {
            throw new InvalidArgumentException('Redirect URI is not registered.');
        }

        return $this->scopeAuthorizationEvaluator->authorize($request->scope, $registeredScopes);
    }
}
