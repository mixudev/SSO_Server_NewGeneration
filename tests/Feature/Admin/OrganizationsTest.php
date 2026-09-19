<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_index_requires_permission_and_searches_real_records(): void
    {
        $admin = $this->authorizedAdmin();
        Organization::factory()->create(['name' => 'Acme Identity', 'slug' => 'acme-identity']);
        Organization::factory()->create(['name' => 'Other Org', 'slug' => 'other-org']);

        $this->actingAs($admin)->get(route('admin.organizations.index', ['search' => 'acme']))
            ->assertOk()
            ->assertSee('Acme Identity')
            ->assertDontSee('Other Org');

        $operator = User::factory()->create();
        $operator->givePermissionTo('admin.dashboard.view');
        $this->actingAs($operator)->get(route('admin.organizations.index'))->assertForbidden();
    }

    public function test_authorized_user_can_create_organization_and_audit_its_boundary(): void
    {
        $admin = $this->authorizedAdmin();

        $this->actingAs($admin)->post(route('admin.organizations.store'), [
            'name' => 'Created Organization',
            'slug' => 'created-organization',
        ])->assertRedirect();

        $organization = Organization::query()->where('slug', 'created-organization')->firstOrFail();
        $this->assertSame('active', $organization->status);
        $this->assertDatabaseHas('security_events', [
            'event' => 'ORGANIZATION_CREATED',
            'organization_id' => $organization->getKey(),
            'subject' => $organization->getKey(),
        ]);
    }

    public function test_user_without_manage_permission_cannot_create_or_update_organization(): void
    {
        $operator = User::factory()->create();
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('organizations.view', 'web');
        $operator->givePermissionTo(['admin.dashboard.view', 'organizations.view']);
        $organization = Organization::factory()->create();

        $this->actingAs($operator)->post(route('admin.organizations.store'), [
            'name' => 'Denied Organization',
            'slug' => 'denied-organization',
        ])->assertForbidden();

        $this->actingAs($operator)->put(route('admin.organizations.update', $organization), [
            'name' => 'Denied Update',
            'slug' => $organization->slug,
            'status' => 'active',
        ])->assertForbidden();
    }

    public function test_organization_detail_shows_application_count_without_secret_fields(): void
    {
        $admin = $this->authorizedAdmin();
        $organization = Organization::factory()->create();
        Application::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Acme Client',
            'client_type' => 'confidential_web',
        ]);

        $this->actingAs($admin)->get(route('admin.organizations.show', $organization))
            ->assertOk()
            ->assertSee('Registered clients')
            ->assertSee('Acme')
            ->assertSee('Confidential Web')
            ->assertDontSee('Registered resources')
            ->assertDontSee('password_hash')
            ->assertDontSee('client_secret');
    }

    public function test_organization_update_validates_status_slug_and_audits(): void
    {
        $admin = $this->authorizedAdmin();
        $organization = Organization::factory()->create(['slug' => 'original-org']);

        $this->actingAs($admin)->put(route('admin.organizations.update', $organization), [
            'name' => 'Updated Organization',
            'slug' => 'updated-org',
            'status' => 'suspended',
        ])->assertRedirect(route('admin.organizations.show', $organization));

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Organization',
            'slug' => 'updated-org',
            'status' => 'suspended',
        ]);
        $this->assertDatabaseHas('security_events', [
            'event' => 'ORGANIZATION_UPDATED',
            'organization_id' => $organization->id,
            'subject' => $organization->id,
        ]);

        $this->actingAs($admin)->put(route('admin.organizations.update', $organization), [
            'name' => '<script>alert(1)</script>',
            'slug' => 'bad slug',
            'status' => 'unknown',
        ])->assertSessionHasErrors(['slug', 'status']);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Organization',
            'slug' => 'updated-org',
            'status' => 'suspended',
        ]);
    }

    private function authorizedAdmin(): User
    {
        $permissions = collect(['admin.dashboard.view', 'organizations.view', 'organizations.manage'])
            ->map(fn (string $name) => Permission::findOrCreate($name, 'web'));
        $role = Role::findOrCreate('platform_admin', 'web');
        $role->syncPermissions($permissions);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
