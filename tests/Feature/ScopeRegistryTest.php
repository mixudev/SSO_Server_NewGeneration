<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationScope;
use App\Models\Organization;
use App\Models\Scope;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopeRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_scope_is_tenant_bound_through_application(): void
    {
        $application = Application::factory()->create();
        $scope = Scope::factory()->create(['name' => 'account:read']);
        $assignment = ApplicationScope::factory()->create([
            'application_id' => $application->id,
            'scope_id' => $scope->id,
        ]);

        $this->assertTrue($assignment->application->organization->is($application->organization));
        $this->assertTrue($assignment->scope->is($scope));
        $this->assertTrue($application->scopes->contains($scope));
        $this->assertTrue($application->redirectUris()->doesntExist());
    }

    public function test_scope_name_is_unique_at_database_boundary(): void
    {
        Scope::factory()->create(['name' => 'account:read']);

        $this->expectException(QueryException::class);
        Scope::factory()->create(['name' => 'account:read']);
    }

    public function test_application_scope_assignment_is_unique(): void
    {
        $application = Application::factory()->create();
        $scope = Scope::factory()->create();
        ApplicationScope::factory()->create([
            'application_id' => $application->id,
            'scope_id' => $scope->id,
        ]);

        $this->expectException(QueryException::class);
        ApplicationScope::factory()->create([
            'application_id' => $application->id,
            'scope_id' => $scope->id,
        ]);
    }

    public function test_same_scope_can_be_assigned_to_applications_in_different_organizations(): void
    {
        $scope = Scope::factory()->create();
        $firstApplication = Application::factory()->create(['organization_id' => Organization::factory()]);
        $secondApplication = Application::factory()->create(['organization_id' => Organization::factory()]);

        ApplicationScope::factory()->create([
            'application_id' => $firstApplication->id,
            'scope_id' => $scope->id,
        ]);
        ApplicationScope::factory()->create([
            'application_id' => $secondApplication->id,
            'scope_id' => $scope->id,
        ]);

        $this->assertCount(2, ApplicationScope::where('scope_id', $scope->id)->get());
    }
}
