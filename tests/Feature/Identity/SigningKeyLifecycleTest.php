<?php

namespace Tests\Feature\Identity;

use App\Infrastructure\Identity\LocalRsaKeyManager;
use App\Models\Identity\SigningKey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SigningKeyLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_an_active_rs256_key(): void
    {
        $key = (new LocalRsaKeyManager)->generate();

        $this->assertSame('RS256', $key->algorithm);
        $this->assertSame('active', $key->status);
        $this->assertSame(1, $key->active_slot);
        $this->assertNotEmpty($key->kid);
        $this->assertStringStartsWith('-----BEGIN PUBLIC KEY-----', $key->public_key);
        $this->assertNotEmpty($key->private_key);
    }

    public function test_rotation_retires_previous_key_and_keeps_one_active_key(): void
    {
        $manager = new LocalRsaKeyManager;
        $first = $manager->generate();
        $second = $manager->rotate();

        $this->assertNotSame($first->kid, $second->kid);
        $this->assertSame('retired', $first->refresh()->status);
        $this->assertNull($first->active_slot);
        $this->assertSame('active', $second->status);
        $this->assertSame(1, SigningKey::where('active_slot', 1)->count());
        $this->assertSame(1, SigningKey::where('status', 'active')->count());
    }

    public function test_private_key_is_not_exposed_by_model_serialization(): void
    {
        $key = (new LocalRsaKeyManager)->generate();

        $this->assertArrayNotHasKey('private_key', $key->toArray());
    }

    public function test_active_key_lookup_fails_closed_when_no_key_exists(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new LocalRsaKeyManager)->active();
    }
}
