<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

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
            'subject' => (string) $target->id,
            'actor' => (string) $admin->id,
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
