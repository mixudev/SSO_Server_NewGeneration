<?php

namespace Tests\Unit\Domain\OAuth;

use App\Domain\Applications\Services\RedirectUriValidator;
use App\Domain\Applications\Services\ScopeAuthorizationEvaluator;
use App\Domain\Applications\Services\ScopeNameValidator;
use App\Domain\Applications\Services\ScopeSetNormalizer;
use App\Domain\OAuth\Data\AuthorizationRequestData;
use App\Domain\OAuth\Data\OAuthClientData;
use App\Domain\OAuth\Services\AuthorizationRequestValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AuthorizationRequestValidatorTest extends TestCase
{
    public function test_valid_request_returns_normalized_allowed_scopes(): void
    {
        $result = $this->validator()->validate(
            $this->request(),
            $this->client(),
            true,
            true,
            ['https://client.example/callback'],
            [
                'email' => ['allowed' => true, 'status' => 'active'],
                'account:read' => ['allowed' => true, 'status' => 'active'],
            ],
        );

        $this->assertSame(['account:read', 'email'], $result);
    }

    public function test_rejects_redirect_variants_and_inactive_boundaries(): void
    {
        $cases = [
            ['https://client.example/callback.evil', true, true],
            ['https://client.example/callback#fragment', true, true],
            ['https://client.example/callback', false, true],
            ['https://client.example/callback', true, false],
        ];

        foreach ($cases as [$redirectUri, $applicationActive, $organizationActive]) {
            try {
                $this->validator()->validate(
                    $this->request(redirectUri: $redirectUri),
                    $this->client(),
                    $applicationActive,
                    $organizationActive,
                    ['https://client.example/callback'],
                    ['email' => ['allowed' => true, 'status' => 'active']],
                );
                $this->fail('Expected invalid authorization request.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_rejects_response_type_pkce_downgrade_and_scope_escalation(): void
    {
        foreach ([
            $this->request(responseType: 'token'),
            $this->request(codeChallengeMethod: 'plain'),
            $this->request(codeChallenge: 'short'),
            $this->request(scope: 'email account:write'),
        ] as $request) {
            try {
                $this->validator()->validate($request, $this->client(), true, true, ['https://client.example/callback'], ['email' => ['allowed' => true, 'status' => 'active']]);
                $this->fail('Expected invalid authorization request.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_rejects_duplicate_query_parameters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AuthorizationRequestData::fromQueryString('client_id=client&client_id=other');
    }

    private function validator(): AuthorizationRequestValidator
    {
        return new AuthorizationRequestValidator(
            new RedirectUriValidator,
            new ScopeAuthorizationEvaluator(new ScopeSetNormalizer(new ScopeNameValidator)),
        );
    }

    private function client(): OAuthClientData
    {
        return new OAuthClientData('client', null, false, false);
    }

    private function request(
        string $redirectUri = 'https://client.example/callback',
        string $responseType = 'code',
        string $scope = 'email account:read',
        string $codeChallengeMethod = 'S256',
        string $codeChallenge = 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
    ): AuthorizationRequestData {
        return new AuthorizationRequestData('client', $redirectUri, $responseType, $scope, 'state', $codeChallenge, $codeChallengeMethod);
    }
}
