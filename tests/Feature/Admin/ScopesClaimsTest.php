<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\Identity\Claim;
use App\Models\Identity\Scope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScopesClaimsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_pages_require_separate_view_permissions(): void
    {
        $admin = $this->admin();
        Scope::factory()->create(['name' => 'account:read']);
        Claim::factory()->create(['key' => 'user.email']);

        $this->actingAs($admin)->get(route('admin.scopes.index'))->assertOk()->assertSee('account:read');
        $this->actingAs($admin)->get(route('admin.claims.index'))->assertOk()->assertSee('user.email');

        $operator = User::factory()->create();
        $operator->givePermissionTo('admin.dashboard.view');
        $this->actingAs($operator)->get(route('admin.scopes.index'))->assertForbidden();
        $this->actingAs($operator)->get(route('admin.claims.index'))->assertForbidden();
    }

    public function test_scope_and_claim_creation_uses_registry_validators_and_allowlists(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.scopes.store'), [
            'name' => 'account:write', 'description' => 'Write access', 'category' => 'custom', 'risk_level' => 'high',
        ])->assertRedirect(route('admin.scopes.index'));
        $this->assertDatabaseHas('scopes', ['name' => 'account:write', 'risk_level' => 'high']);
        $this->assertDatabaseHas('security_events', ['event' => 'SCOPE_CREATED']);

        $this->actingAs($admin)->post(route('admin.claims.store'), [
            'key' => 'user.department', 'source' => 'user.department', 'value_type' => 'string', 'sensitivity' => 'personal',
        ])->assertRedirect(route('admin.claims.index'));
        $this->assertDatabaseHas('claims', ['key' => 'user.department']);
        $this->assertDatabaseHas('security_events', ['event' => 'CLAIM_CREATED']);

        $this->actingAs($admin)->post(route('admin.scopes.store'), [
            'name' => 'bad scope', 'category' => 'custom', 'risk_level' => 'admin',
        ])->assertSessionHasErrors(['name', 'risk_level']);
        $this->actingAs($admin)->post(route('admin.claims.store'), [
            'key' => 'bad key!', 'source' => 'user.email', 'value_type' => 'object', 'sensitivity' => 'secret',
        ])->assertSessionHasErrors(['key', 'value_type', 'sensitivity']);
    }

    public function test_scope_update_rejects_system_and_active_application_references(): void
    {
        $admin = $this->admin();
        $systemScope = Scope::factory()->create(['name' => 'system:read', 'is_system' => true]);

        $this->actingAs($admin)->put(route('admin.scopes.update', $systemScope), [
            'name' => 'system:renamed', 'description' => '', 'risk_level' => 'high', 'status' => 'active',
        ])->assertStatus(422);

        $scope = Scope::factory()->create(['name' => 'account:active']);
        $application = Application::factory()->create(['status' => 'active']);
        $application->scopes()->attach($scope->getKey(), ['allowed' => true, 'consent_required' => true]);

        $this->actingAs($admin)->put(route('admin.scopes.update', $scope), [
            'name' => 'account:active', 'description' => '', 'risk_level' => 'high', 'status' => 'revoked',
        ])->assertStatus(422);

        $this->assertDatabaseHas('scopes', ['id' => $scope->getKey(), 'status' => 'active']);
    }

    public function test_system_flag_is_not_request_assignable_and_output_escapes_html(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.scopes.store'), [
            'name' => 'profile:read', 'description' => '<script>alert(1)</script>', 'category' => 'custom', 'risk_level' => 'low', 'is_system' => true,
        ])->assertRedirect();
        $scope = Scope::query()->where('name', 'profile:read')->firstOrFail();
        $this->assertFalse($scope->is_system);
        $this->actingAs($admin)->get(route('admin.scopes.index'))->assertDontSee('<script>alert(1)</script>', false);
    }

    private function admin(): User
    {
        $permissions = collect(['admin.dashboard.view', 'scopes.view', 'scopes.manage', 'claims.view', 'claims.manage'])
            ->map(fn (string $name) => Permission::findOrCreate($name, 'web'));
        $role = Role::findOrCreate('platform_admin', 'web');
        $role->syncPermissions($permissions);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
