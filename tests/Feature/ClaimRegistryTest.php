<?php

namespace Tests\Feature;

use App\Models\Claim;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_uses_ulid_and_persists_security_metadata(): void
    {
        $claim = Claim::factory()->create();

        $this->assertSame(26, strlen($claim->getKey()));
        $this->assertSame('user.email', $claim->key);
        $this->assertSame('user.email', $claim->source);
        $this->assertSame('personal', $claim->sensitivity);
        $this->assertSame('active', $claim->status);
    }

    public function test_claim_key_is_unique_at_database_boundary(): void
    {
        Claim::factory()->create(['key' => 'user.email']);

        $this->expectException(QueryException::class);
        Claim::factory()->create(['key' => 'user.email']);
    }
}
