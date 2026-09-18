<?php

namespace Tests\Unit\Domain\Applications;

use App\Domain\Applications\Services\ScopeNameValidator;
use App\Domain\Applications\Services\ScopeSetNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ScopeSetNormalizerTest extends TestCase
{
    private ScopeSetNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new ScopeSetNormalizer(new ScopeNameValidator);
    }

    public function test_normalizes_whitespace_duplicates_and_order(): void
    {
        $this->assertSame(
            ['account:read', 'email', 'openid'],
            $this->normalizer->normalize("openid  email\taccount:read openid"),
        );
    }

    public function test_accepts_reserved_scopes_and_namespaced_custom_scopes(): void
    {
        $this->assertSame(
            ['account:read', 'profile'],
            $this->normalizer->normalize('profile account:read'),
        );
    }

    public function test_rejects_unnamespaced_custom_scope_names(): void
    {
        foreach (['admin', 'Account:read', 'account', 'account:', 'account:read/write', 'account:read%00'] as $scope) {
            try {
                $this->normalizer->normalize($scope);
                $this->fail('Invalid scope was accepted: '.$scope);
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }
}
