<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationRedirectUri;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationCredentialTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_application_cannot_issue_a_credential(): void
    {
        [$admin, $application] = $this->application('draft', 'confidential_web');

        $this->actingAs($admin)
            ->post(route('admin.applications.credentials.issue', $application))
            ->assertStatus(422);

        $this->assertDatabaseCount('oauth_clients', 0);
    }

    public function test_active_confidential_application_returns_secret_once_and_persists_only_hash(): void
    {
        [$admin, $application] = $this->application('active', 'confidential_web');

        $this->actingAs($admin)
            ->get(route('admin.applications.show', $application))
            ->assertOk()
            ->assertSee('issue-client-modal');

        $response = $this->actingAs($admin)
            ->post(route('admin.applications.credentials.issue', $application));

        $response->assertOk()->assertViewIs('pages.admin.applications.credential');
        $secret = $response->viewData('result')['client_secret'];
        $this->assertNotEmpty($secret);
        $this->assertDatabaseHas('application_credentials', ['application_id' => $application->getKey(), 'generation' => 1]);
        $client = Client::query()->firstOrFail();
        $this->assertNotSame($secret, $client->secret);
        $this->assertStringContainsString($secret, $response->getContent());

        $this->actingAs($admin)
            ->get(route('admin.applications.show', $application))
            ->assertOk()
            ->assertSee('rotate-client-modal')
            ->assertSee('revoke-client-modal')
            ->assertDontSee($secret);
    }

    public function test_public_application_has_no_secret_and_rotation_is_rejected(): void
    {
        [$admin, $application] = $this->application('active', 'public_spa');

        $response = $this->actingAs($admin)
            ->post(route('admin.applications.credentials.issue', $application));

        $response->assertOk();
        $this->assertNull($response->viewData('result')['client_secret']);
        $this->assertNull(Client::query()->firstOrFail()->secret);

        $this->actingAs($admin)
            ->post(route('admin.applications.credentials.rotate', $application))
            ->assertStatus(422);
    }

    public function test_runtime_repository_rejects_suspended_application_and_organization(): void
    {
        [$admin, $application] = $this->application('active', 'confidential_web');
        $this->actingAs($admin)->post(route('admin.applications.credentials.issue', $application));
        $client = Client::query()->firstOrFail();
        $repository = app(ClientRepository::class);

        $this->assertNotNull($repository->findActive($client->getKey()));
        $application->update(['status' => 'suspended']);
        $this->assertNull($repository->findActive($client->getKey()));
        $application->update(['status' => 'active']);
        $application->organization->update(['status' => 'suspended']);
        $this->assertNull($repository->findActive($client->getKey()));
    }

    public function test_rotation_replaces_secret_and_revoke_revokes_client(): void
    {
        [$admin, $application] = $this->application('active', 'confidential_web');

        $issue = $this->actingAs($admin)->post(route('admin.applications.credentials.issue', $application));
        $firstSecret = $issue->viewData('result')['client_secret'];

        $rotation = $this->actingAs($admin)->post(route('admin.applications.credentials.rotate', $application));
        $secondSecret = $rotation->viewData('result')['client_secret'];
        $this->assertNotSame($firstSecret, $secondSecret);
        $this->assertDatabaseHas('application_credentials', ['application_id' => $application->getKey(), 'generation' => 2]);

        $client = Client::query()->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.applications.credentials.revoke', $application))->assertRedirect();
        $this->assertTrue($client->fresh()->revoked);
        $this->assertDatabaseHas('application_credentials', ['application_id' => $application->getKey(), 'status' => 'revoked']);
    }

    /** @return array{0: User, 1: Application} */
    private function application(string $status, string $clientType): array
    {
        $permissions = collect([
            'admin.dashboard.view',
            'applications.credentials.issue',
            'applications.credentials.rotate',
            'applications.view',
        ])->map(fn (string $name): Permission => Permission::findOrCreate($name, 'web'));
        $role = Role::findOrCreate('credential_admin', 'web');
        $role->syncPermissions($permissions);
        $admin = User::factory()->create();
        $admin->assignRole($role);
        $application = Application::factory()->create([
            'status' => $status,
            'client_type' => $clientType,
            'organization_id' => Organization::factory()->create(['status' => 'active'])->getKey(),
        ]);
        ApplicationRedirectUri::factory()->create(['application_id' => $application->getKey()]);

        return [$admin, $application];
    }
}
