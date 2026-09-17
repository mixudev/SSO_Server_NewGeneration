<?php

namespace Tests\Unit;

use App\Domain\Applications\Services\ClaimKeyValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ClaimKeyValidatorTest extends TestCase
{
    private ClaimKeyValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ClaimKeyValidator;
    }

    public function test_accepts_reserved_and_canonical_claim_keys(): void
    {
        $this->assertSame('email', $this->validator->validate('email'));
        $this->assertSame('user.email', $this->validator->validate('user.email'));
        $this->assertSame('organization_id', $this->validator->validate('organization_id'));
    }

    public function test_rejects_malformed_or_ambiguous_claim_keys(): void
    {
        foreach (['', ' user.email ', 'User.email', 'user email', 'user/email', 'user..email', 'user.*', 'user=admin', "user\x00email"] as $key) {
            try {
                $this->validator->validate($key);
                $this->fail('Invalid claim key was accepted: '.$key);
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }
}
