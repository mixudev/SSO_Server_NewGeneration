<?php

namespace Tests\Unit\Domain\Identity;

use App\Domain\Identity\Services\ClaimPolicyScopeMapper;
use PHPUnit\Framework\TestCase;

class ClaimPolicyScopeMapperTest extends TestCase
{
    public function test_maps_standard_identity_claims_to_required_scopes(): void
    {
        $mapper = new ClaimPolicyScopeMapper;

        $this->assertSame(['email'], $mapper->requiredScopes('user.email'));
        $this->assertSame(['email'], $mapper->requiredScopes('user.email_verified'));
        $this->assertSame(['profile'], $mapper->requiredScopes('user.name'));
    }

    public function test_unknown_claim_is_not_granted_implicitly(): void
    {
        $this->assertSame([], (new ClaimPolicyScopeMapper)->requiredScopes('custom.admin_flag'));
    }
}
