<?php

namespace Tests\Feature\Identity;

use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FoundationBaselineTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_available(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_health_endpoint_returns_minimal_status(): void
    {
        $this->get('/health/live')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect();
    }

    public function test_authenticated_root_is_public_landing_page(): void
    {
        $this->actingAs(User::factory()->create(['active' => true, 'status' => 'active']))
            ->get('/')
            ->assertOk();
    }

    public function test_authenticated_non_admin_is_sent_to_the_application_portal(): void
    {
        $this->actingAs(User::factory()->create(['active' => true, 'status' => 'active']))
            ->get(route('authenticated.landing'))
            ->assertRedirect(route('sso.portal'));
    }

    public function test_authenticated_admin_is_sent_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->create(['active' => true, 'status' => 'active']);
        $permission = Permission::findOrCreate('admin.dashboard.view', 'web');
        $role = Role::findOrCreate('platform_admin', 'web');
        $role->givePermissionTo($permission);
        $admin->assignRole($role);

        $this->actingAs($admin)
            ->get(route('authenticated.landing'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_authentication_success_redirects_to_role_aware_landing(): void
    {
        self::assertSame('/app', config('authentication.redirects.login'));
        self::assertSame('/app', config('authentication.redirects.register'));
    }

    public function test_oauth_authorize_route_remains_separate_from_provider_portal(): void
    {
        self::assertTrue(app('router')->getRoutes()->getByName('oauth.authorize') !== null);
        self::assertTrue(app('router')->getRoutes()->getByName('sso.portal') !== null);
        self::assertNotSame(
            app('router')->getRoutes()->getByName('oauth.authorize')->uri(),
            app('router')->getRoutes()->getByName('sso.portal')->uri(),
        );
    }

    public function test_organization_has_secure_public_identifier_and_defaults(): void
    {
        $organization = Organization::factory()->create();

        $this->assertSame(26, strlen($organization->getKey()));
        $this->assertSame('active', $organization->status);
        $this->assertSame([], $organization->settings_json);
    }

    public function test_organization_slug_is_unique_at_database_boundary(): void
    {
        Organization::factory()->create(['slug' => 'acme']);

        $this->expectException(QueryException::class);
        Organization::factory()->create(['slug' => 'acme']);
    }

    public function test_application_belongs_to_an_organization_and_uses_secure_identifier(): void
    {
        $application = Application::factory()->create();

        $this->assertSame(26, strlen($application->getKey()));
        $this->assertTrue($application->organization->is($application->organization));
        $this->assertSame($application->organization_id, $application->organization->getKey());
        $this->assertSame('draft', $application->status);
        $this->assertSame([], $application->session_policy_json);
    }

    public function test_application_slug_may_repeat_in_different_organizations(): void
    {
        $firstOrganization = Organization::factory()->create();
        $secondOrganization = Organization::factory()->create();

        Application::factory()->create([
            'organization_id' => $firstOrganization->id,
            'slug' => 'portal',
        ]);

        $secondApplication = Application::factory()->create([
            'organization_id' => $secondOrganization->id,
            'slug' => 'portal',
        ]);

        $this->assertSame('portal', $secondApplication->slug);
    }

    public function test_application_slug_is_unique_inside_an_organization(): void
    {
        $organization = Organization::factory()->create();
        Application::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'portal',
        ]);

        $this->expectException(QueryException::class);
        Application::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'portal',
        ]);
    }
}
