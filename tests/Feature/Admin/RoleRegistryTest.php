<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_registry_requires_separate_view_permission(): void
    {
        $admin = $this->authorizedUser('roles.view');
        Role::findOrCreate('operator', 'web');

        $this->actingAs($admin)->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('operator');

        $operator = User::factory()->create();
        $operator->givePermissionTo('admin.dashboard.view');

        $this->actingAs($operator)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_authorized_admin_can_create_role_with_allowlisted_permissions(): void
    {
        $admin = $this->authorizedUser('roles.manage');

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'support_operator',
            'permissions' => ['users.view', 'audit.view'],
        ])->assertRedirect(route('admin.roles.index'));

        $role = Role::findByName('support_operator', 'web');
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertTrue($role->hasPermissionTo('audit.view'));
        $this->assertDatabaseHas('security_events', ['event' => 'ROLE_CREATED']);
    }

    public function test_unknown_permission_and_system_role_mutation_are_rejected(): void
    {
        $admin = $this->authorizedUser('roles.manage');
        $systemRole = Role::findOrCreate('platform_admin', 'web');

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'unsafe',
            'permissions' => ['not-a-real-permission'],
        ])->assertSessionHasErrors('permissions.0');

        $this->actingAs($admin)->put(route('admin.roles.update', $systemRole), [
            'name' => 'renamed_admin',
            'permissions' => ['users.view'],
        ])->assertStatus(422);

        $this->assertDatabaseMissing('roles', ['name' => 'renamed_admin']);
    }

    private function authorizedUser(string $permission): User
    {
        foreach (['admin.dashboard.view', 'roles.view', 'roles.manage', 'users.view', 'audit.view'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', $permission]);

        return $user;
    }
}
