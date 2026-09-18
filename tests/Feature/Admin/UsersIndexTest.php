<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsersIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_requires_view_permission(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('admin.dashboard.view', 'web');
        $user->givePermissionTo('admin.dashboard.view');

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_authorized_user_can_search_users_without_secret_fields(): void
    {
        $admin = $this->authorizedUser();
        $role = Role::findOrCreate('operator', 'web');
        $target = User::factory()->create(['name' => 'Target Operator', 'email' => 'target@example.test']);
        $target->assignRole($role);
        User::factory()->create(['name' => 'Other User', 'email' => 'other@example.test']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'target@example.test']));

        $response->assertOk()
            ->assertSee('Target Operator')
            ->assertSee('operator')
            ->assertDontSee('Other User')
            ->assertDontSee('password_hash')
            ->assertDontSee('remember_token');
    }

    private function authorizedUser(): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('users.view', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', 'users.view']);

        return $user;
    }
}
