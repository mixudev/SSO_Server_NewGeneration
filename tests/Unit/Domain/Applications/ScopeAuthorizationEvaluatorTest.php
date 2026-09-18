<?php

namespace Tests\Unit\Domain\Applications;

use App\Domain\Applications\Services\ScopeAuthorizationEvaluator;
use App\Domain\Applications\Services\ScopeNameValidator;
use App\Domain\Applications\Services\ScopeSetNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ScopeAuthorizationEvaluatorTest extends TestCase
{
    private ScopeAuthorizationEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new ScopeAuthorizationEvaluator(
            new ScopeSetNormalizer(new ScopeNameValidator),
        );
    }

    public function test_authorizes_only_active_allowed_scopes(): void
    {
        $this->assertSame(
            ['account:read', 'openid'],
            $this->evaluator->authorize('openid account:read', [
                'openid' => ['allowed' => true, 'status' => 'active'],
                'account:read' => ['allowed' => true, 'status' => 'active'],
            ]),
        );
    }

    public function test_rejects_unknown_scope_instead_of_downgrading_request(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->evaluator->authorize('openid account:write', [
            'openid' => ['allowed' => true, 'status' => 'active'],
        ]);
    }

    public function test_rejects_disallowed_and_inactive_scopes(): void
    {
        foreach ([
            ['account:read' => ['allowed' => false, 'status' => 'active']],
            ['account:read' => ['allowed' => true, 'status' => 'revoked']],
        ] as $registeredScopes) {
            try {
                $this->evaluator->authorize('account:read', $registeredScopes);
                $this->fail('Unauthorized scope was accepted.');
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_rejects_scope_registration_with_malformed_state(): void
    {
        $registrations = [
            ['allowed' => 1, 'status' => 'active'],
            ['allowed' => true, 'status' => 'ACTIVE'],
            ['allowed' => true, 'status' => null],
        ];

        foreach ($registrations as $registration) {
            try {
                $this->evaluator->authorize('account:read', ['account:read' => $registration]);
                $this->fail('Malformed scope registration was accepted.');
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }
}
