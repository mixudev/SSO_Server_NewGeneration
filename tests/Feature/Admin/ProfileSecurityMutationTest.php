<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProfileSecurityMutationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('security-defense.middleware.payload_scanner.enabled', false);
        Permission::findOrCreate('admin.dashboard.view', 'web');
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.dashboard.view');

        $this->actingAs($user)
            ->post(route('admin.profile.password.update'), [
                'password' => 'New-password-123',
                'password_confirmation' => 'New-password-123',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_revoke_other_sessions_requires_password_step_up(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.dashboard.view');

        $this->actingAs($user)
            ->post(route('admin.profile.security.sessions.destroy-others'))
            ->assertSessionHasErrors('password');
    }
}
