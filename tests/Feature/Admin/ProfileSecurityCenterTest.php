<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProfileSecurityCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('security-defense.middleware.payload_scanner.enabled', false);
        Permission::findOrCreate('admin.dashboard.view', 'web');
    }

    public function test_profile_security_center_renders_package_security_routes_without_secrets(): void
    {
        $user = User::factory()->create(['name' => 'Security Operator']);
        $user->givePermissionTo('admin.dashboard.view');

        $response = $this->actingAs($user)->get(route('admin.profile.security'));

        $response->assertOk()
            ->assertSee('Security center')
            ->assertSee(route('admin.profile.security.two-factor'), false)
            ->assertSee(route('admin.profile.security.passkeys.options'), false)
            ->assertDontSee(route('admin.profile.security.password-reset'), false)
            ->assertDontSee(route('password.request'), false)
            ->assertDontSee(route('auth.sessions.index'), false)
            ->assertDontSee('recovery_codes')
            ->assertDontSee('private_key');
    }

    public function test_profile_security_center_is_denied_without_dashboard_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.profile.show'))->assertForbidden();
    }
}
