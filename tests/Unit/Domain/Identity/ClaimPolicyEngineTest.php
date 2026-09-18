<?php

namespace Tests\Unit\Domain\Identity;

use App\Domain\Identity\Services\ClaimPolicyEngine;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ClaimPolicyEngineTest extends TestCase
{
    public function test_resolves_only_claims_allowed_by_requested_scopes(): void
    {
        $engine = new ClaimPolicyEngine;

        $claims = $engine->resolve(
            [
                'version' => 3,
                'claims' => [
                    ['key' => 'user.email', 'source' => 'email', 'scopes' => ['email'], 'value_type' => 'string'],
                    ['key' => 'user.name', 'source' => 'name', 'scopes' => ['profile'], 'value_type' => 'string'],
                ],
            ],
            ['email'],
            ['email' => 'user@example.test', 'name' => 'Admin'],
        );

        $this->assertSame(['user.email' => 'user@example.test'], $claims);
    }

    public function test_does_not_emit_missing_sources(): void
    {
        $claims = (new ClaimPolicyEngine)->resolve(
            [
                'version' => 1,
                'claims' => [
                    ['key' => 'user.email', 'source' => 'email', 'scopes' => ['email'], 'value_type' => 'string'],
                ],
            ],
            ['email'],
            [],
        );

        $this->assertSame([], $claims);
    }

    public function test_rejects_invalid_policy_version(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ClaimPolicyEngine)->resolve(['version' => 0, 'claims' => []], [], []);
    }

    public function test_rejects_claim_value_type_mismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ClaimPolicyEngine)->resolve(
            [
                'version' => 1,
                'claims' => [
                    ['key' => 'user.active', 'source' => 'active', 'scopes' => ['profile'], 'value_type' => 'boolean'],
                ],
            ],
            ['profile'],
            ['active' => 'yes'],
        );
    }

    public function test_rejects_unknown_claim_value_type_before_source_lookup(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ClaimPolicyEngine)->resolve(
            [
                'version' => 1,
                'claims' => [
                    ['key' => 'user.email', 'source' => 'email', 'scopes' => ['email'], 'value_type' => 'object'],
                ],
            ],
            ['email'],
            [],
        );
    }

    public function test_rejects_duplicate_claim_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ClaimPolicyEngine)->resolve(
            [
                'version' => 1,
                'claims' => [
                    ['key' => 'user.email', 'source' => 'email', 'scopes' => ['email'], 'value_type' => 'string'],
                    ['key' => 'user.email', 'source' => 'alternate_email', 'scopes' => ['email'], 'value_type' => 'string'],
                ],
            ],
            ['email'],
            ['email' => 'one@example.test', 'alternate_email' => 'two@example.test'],
        );
    }
}
