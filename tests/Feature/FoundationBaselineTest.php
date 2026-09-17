<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Organization;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
