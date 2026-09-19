<?php

namespace Tests\Feature;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OidcJwksRotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_jwks_keeps_previous_key_during_overlap_window(): void
    {
        $keys = app(KeyManagerInterface::class);
        $old = $keys->generate();
        $new = $keys->rotate();

        $kids = $this->getJson('/.well-known/jwks.json')
            ->assertOk()
            ->json('keys.*.kid');

        $this->assertContains($old->kid, $kids);
        $this->assertContains($new->kid, $kids);
    }
}
