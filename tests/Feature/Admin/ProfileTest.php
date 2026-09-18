<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('security-defense.middleware.payload_scanner.enabled', false);
        Permission::findOrCreate('admin.dashboard.view', 'web');
    }

    public function test_guest_cannot_access_profile(): void
    {
        $this->get(route('admin.profile.show'))->assertRedirect();
    }

    public function test_authenticated_operator_can_view_profile_without_secret_fields(): void
    {
        $user = User::factory()->create(['name' => 'Platform Operator']);
        $user->givePermissionTo('admin.dashboard.view');

        $this->actingAs($user)
            ->get(route('admin.profile.show'))
            ->assertOk()
            ->assertSee('Platform Operator')
            ->assertSee('Account security')
            ->assertDontSee('password_hash')
            ->assertDontSee('remember_token')
            ->assertDontSee('clientDataJSON');
    }

    public function test_operator_can_update_allowed_profile_fields(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.dashboard.view');

        $this->actingAs($user)
            ->put(route('admin.profile.update'), [
                'name' => 'Updated Operator',
                'email' => 'updated@example.test',
            ])
            ->assertRedirect(route('admin.profile.show'))
            ->assertSessionHas('status', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Operator',
            'email' => 'updated@example.test',
        ]);
    }

    public function test_profile_update_rejects_invalid_input(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.dashboard.view');

        $this->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.update'), ['name' => 'x', 'email' => 'not-an-email'])
            ->assertSessionHasErrors(['name', 'email']);
    }
}
