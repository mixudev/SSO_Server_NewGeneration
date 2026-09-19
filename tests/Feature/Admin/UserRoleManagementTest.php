<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\SecurityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_receive_uuid_and_routes_never_use_internal_id(): void
    {
        $user = User::factory()->create();

        $this->assertNotSame((string) $user->id, $user->uuid);
        $this->assertSame('uuid', $user->getRouteKeyName());
        $route = route('admin.users.show', $user);
        $this->assertStringContainsString($user->uuid, $route);
        $this->assertNotSame((string) $user->id, basename(parse_url($route, PHP_URL_PATH)));
    }

    public function test_users_without_manage_permission_cannot_update_user(): void
    {
        $admin = $this->authorizedUser('users.view');
        $target = User::factory()->create(['name' => 'Original']);
        Role::findOrCreate('operator', 'web');

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => 'Changed',
            'email' => $target->email,
            'role' => 'operator',
        ])->assertForbidden();

        $this->assertSame('Original', $target->fresh()->name);
    }

    public function test_authorized_user_can_assign_existing_role_and_audit_event_is_written(): void
    {
        $admin = $this->authorizedUser('users.manage');
        $target = User::factory()->create();
        Role::findOrCreate('operator', 'web');

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => 'Updated User',
            'email' => $target->email,
            'role' => 'operator',
        ])->assertRedirect(route('admin.users.show', $target));

        $this->assertTrue($target->fresh()->hasRole('operator'));
        $this->assertDatabaseHas('security_events', [
            'event' => 'USER_ROLE_UPDATED',
            'subject' => (string) $target->uuid,
            'actor' => (string) $admin->uuid,
        ]);
    }

    public function test_unknown_role_and_direct_permission_assignment_are_rejected(): void
    {
        $admin = $this->authorizedUser('users.manage');
        $target = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'admin.dashboard.view',
            'permissions' => ['*'],
        ])->assertSessionHasErrors('role');

        $this->assertCount(0, $target->fresh()->getAllPermissions());
    }

    public function test_user_detail_limits_activity_to_five_and_exposes_all_activity_route(): void
    {
        $admin = $this->authorizedUser('users.view');
        $target = User::factory()->create();

        foreach (range(1, 6) as $number) {
            SecurityEvent::query()->create([
                'event' => 'TEST_EVENT_'.$number,
                'subject' => $target->uuid,
                'risk' => 'low',
                'occurred_at' => now()->subMinutes($number),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.users.show', $target));

        $response->assertOk()->assertSee('TEST_EVENT_1')->assertDontSee('TEST_EVENT_6');
        $this->actingAs($admin)->get(route('admin.users.activity', $target))->assertOk()->assertSee('TEST_EVENT_6');
    }

    public function test_authorized_admin_can_open_user_bento_detail_without_inline_edit_form(): void
    {
        $admin = $this->authorizedUser('users.manage');
        $admin->givePermissionTo('users.view');
        $target = User::factory()->create(['name' => 'Target User']);

        $this->actingAs($admin)->get(route('admin.users.show', $target))
            ->assertOk()
            ->assertSee('Target User')
            ->assertSee('Account identity')
            ->assertSee('Change password')
            ->assertSee('Delete user')
            ->assertSee('Edit user')
            ->assertSee('form="change-password-form"', false)
            ->assertSee('form="toggle-status-form"', false)
            ->assertSee(route('admin.users.update', $target), false);
    }

    public function test_authorized_admin_can_delete_user_with_password_confirmation(): void
    {
        $admin = $this->authorizedUser('users.manage');
        $target = User::factory()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $target), [
            'current_password' => 'password',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseHas('security_events', [
            'event' => 'USER_DELETED',
            'subject' => (string) $target->uuid,
        ]);
    }

    public function test_user_delete_without_admin_password_is_rejected(): void
    {
        $admin = $this->authorizedUser('users.manage');
        $target = User::factory()->create();

        $this->actingAs($admin)->from(route('admin.users.show', $target))
            ->delete(route('admin.users.destroy', $target))
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_authorized_admin_can_change_password_without_exposing_password(): void
    {
        $admin = $this->authorizedUser('users.manage');
        $target = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.users.password.update', $target), [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ])->assertRedirect(route('admin.users.show', $target));

        $this->assertDatabaseMissing('users', ['password' => 'NewSecurePassword123!']);
        $this->assertDatabaseHas('security_events', ['event' => 'USER_PASSWORD_CHANGED', 'subject' => (string) $target->uuid]);
    }

    public function test_authorized_admin_can_send_reset_link_without_receiving_token(): void
    {
        $admin = $this->authorizedUser('users.manage');
        $target = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.password.reset-link', $target))
            ->assertRedirect(route('admin.users.show', $target))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $target->email]);
    }

    public function test_numeric_user_id_cannot_open_user_detail(): void
    {
        $admin = $this->authorizedUser('users.view');
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/users/'.$target->id)
            ->assertNotFound();
    }

    public function test_authorized_admin_can_deactivate_user_and_inactive_login_is_rejected(): void
    {
        $admin = $this->authorizedUser('users.manage');
        $target = User::factory()->create([
            'email' => 'banned@example.test',
            'password' => 'ValidPassword123!',
        ]);

        $this->actingAs($admin)->put(route('admin.users.status.update', $target), [
            'active' => 0,
            'current_password' => 'password',
        ])->assertRedirect(route('admin.users.show', $target));

        $target = $target->fresh();
        $this->assertFalse($target->active);
        $this->assertSame('inactive', $target->status);
        $this->assertDatabaseHas('security_events', [
            'event' => 'USER_DEACTIVATED',
            'subject' => (string) $target->uuid,
        ]);

        $this->post(route('logout'));
        $this->post(route('login.perform'), [
            'identifier' => 'banned@example.test',
            'password' => 'ValidPassword123!',
        ])->assertSessionHasErrors('identifier');
        $this->assertGuest();
    }

    public function test_status_change_without_admin_password_is_rejected(): void
    {
        $admin = $this->authorizedUser('users.manage');
        $target = User::factory()->create();

        $this->actingAs($admin)->from(route('admin.users.show', $target))
            ->put(route('admin.users.status.update', $target), ['active' => 0])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue($target->fresh()->active);
    }

    private function authorizedUser(string $permission): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('users.view', 'web');
        Permission::findOrCreate('users.manage', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', $permission]);

        return $user;
    }
}
