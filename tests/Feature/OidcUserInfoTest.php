<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OidcUserInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_userinfo_requires_a_bearer_access_token(): void
    {
        $this->getJson('/oauth/userinfo')->assertUnauthorized();
    }

    public function test_userinfo_returns_allowlisted_claims_for_a_token(): void
    {
        $user = User::factory()->create(['name' => 'Ada Lovelace']);
        Passport::actingAs($user, ['openid', 'profile', 'email']);

        $this->getJson('/oauth/userinfo')
            ->assertOk()
            ->assertJsonPath('sub', (string) $user->uuid)
            ->assertJsonPath('name', 'Ada Lovelace')
            ->assertJsonPath('email', $user->email)
            ->assertJsonMissingPath('password');
    }
}
