<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserLastAdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_platform_admin_cannot_be_demoted(): void
    {
        $admin = $this->authorizedAdmin();
        Role::findOrCreate('operator', 'web');

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'operator',
        ])->assertStatus(422);

        $this->assertTrue($admin->fresh()->hasRole('platform_admin'));
    }

    public function test_last_platform_admin_cannot_be_deleted(): void
    {
        $admin = $this->authorizedAdmin();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['admin.dashboard.view', 'users.view', 'users.manage']);

        $this->actingAs($manager)->from(route('admin.users.show', $admin))->delete(route('admin.users.destroy', $admin), [
            'current_password' => 'password',
        ])->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertTrue($admin->fresh()->hasRole('platform_admin'));
    }

    public function test_admin_can_demote_when_another_platform_admin_remains(): void
    {
        $admin = $this->authorizedAdmin();
        $secondAdmin = User::factory()->create();
        $secondAdmin->assignRole('platform_admin');
        Role::findOrCreate('operator', 'web');

        $this->actingAs($admin)->put(route('admin.users.update', $secondAdmin), [
            'name' => $secondAdmin->name,
            'email' => $secondAdmin->email,
            'role' => 'operator',
        ])->assertRedirect(route('admin.users.show', $secondAdmin));

        $this->assertTrue($admin->fresh()->hasRole('platform_admin'));
        $this->assertTrue($secondAdmin->fresh()->hasRole('operator'));
    }

    public function test_last_platform_admin_cannot_be_deactivated(): void
    {
        $admin = $this->authorizedAdmin();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['admin.dashboard.view', 'users.view', 'users.manage']);

        $this->actingAs($manager)->from(route('admin.users.show', $admin))->put(route('admin.users.status.update', $admin), [
            'active' => 0,
            'current_password' => 'password',
        ])->assertSessionHasErrors('active');

        $this->assertTrue($admin->fresh()->active);
        $this->assertSame('active', $admin->fresh()->status);
        $this->assertTrue($admin->fresh()->hasRole('platform_admin'));
    }

    public function test_platform_admin_can_be_deactivated_when_another_active_admin_remains(): void
    {
        $admin = $this->authorizedAdmin();
        $secondAdmin = $this->authorizedAdmin();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['admin.dashboard.view', 'users.view', 'users.manage']);

        $this->actingAs($manager)->put(route('admin.users.status.update', $secondAdmin), [
            'active' => 0,
            'current_password' => 'password',
        ])->assertRedirect(route('admin.users.show', $secondAdmin));

        $this->assertFalse($secondAdmin->fresh()->active);
        $this->assertSame('inactive', $secondAdmin->fresh()->status);
        $this->assertTrue($admin->fresh()->active);
        $this->assertTrue($admin->fresh()->hasRole('platform_admin'));
    }

    private function authorizedAdmin(): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('users.view', 'web');
        Permission::findOrCreate('users.manage', 'web');
        $role = Role::findOrCreate('platform_admin', 'web');
        $role->syncPermissions(Permission::whereIn('name', ['admin.dashboard.view', 'users.view', 'users.manage'])->get());
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
