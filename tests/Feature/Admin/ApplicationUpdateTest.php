<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationRedirectUri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApplicationUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('security-defense.middleware.payload_scanner.enabled', false);
    }

    public function test_authorized_user_can_update_metadata_and_replace_redirect_uris(): void
    {
        $user = $this->authorizedUser();
        $application = Application::factory()->create(['name' => 'Old name']);
        ApplicationRedirectUri::factory()->create([
            'application_id' => $application->id,
            'uri' => 'https://old.example/callback',
        ]);

        $response = $this->actingAs($user)->put("/admin/applications/{$application->id}", [
            'organization_id' => $application->organization_id,
            'name' => 'New name',
            'slug' => $application->slug,
            'protocol_mode' => $application->protocol_mode,
            'client_type' => $application->client_type,
            'description' => 'Updated description',
            'redirect_uris' => ['HTTPS://NEW.EXAMPLE/callback'],
        ]);

        $response->assertRedirect(route('admin.applications.show', $application));
        $this->assertSame('New name', $application->refresh()->name);
        $this->assertDatabaseHas('application_redirect_uris', [
            'application_id' => $application->id,
            'uri' => 'https://new.example/callback',
        ]);
        $this->assertDatabaseMissing('application_redirect_uris', ['uri' => 'https://old.example/callback']);
    }

    public function test_update_persists_logout_redirect_uris_as_separate_kind(): void
    {
        $user = $this->authorizedUser();
        $application = Application::factory()->create();

        $this->actingAs($user)->put(route('admin.applications.update', $application), [
            'organization_id' => $application->organization_id,
            'name' => $application->name,
            'slug' => $application->slug,
            'protocol_mode' => $application->protocol_mode,
            'client_type' => $application->client_type,
            'redirect_uris' => ['https://client.example/callback'],
            'logout_redirect_uris' => ['HTTPS://CLIENT.EXAMPLE/logout/callback'],
        ])->assertRedirect(route('admin.applications.show', $application));

        $this->assertDatabaseHas('application_redirect_uris', [
            'application_id' => $application->getKey(),
            'kind' => 'logout',
            'uri' => 'https://client.example/logout/callback',
        ]);
    }

    public function test_invalid_update_does_not_change_existing_application(): void
    {
        $user = $this->authorizedUser();
        $application = Application::factory()->create(['name' => 'Original']);

        $this->actingAs($user)->from('/admin/applications/'.$application->id)->put("/admin/applications/{$application->id}", [
            'organization_id' => $application->organization_id,
            'name' => 'Tampered',
            'slug' => $application->slug,
            'protocol_mode' => $application->protocol_mode,
            'client_type' => $application->client_type,
            'redirect_uris' => ['https://client.example/callback#fragment'],
        ])->assertSessionHasErrors('redirect_uris.0');

        $this->assertSame('Original', $application->refresh()->name);
    }

    public function test_user_without_update_permission_is_denied(): void
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.dashboard.view');
        $application = Application::factory()->create();

        $this->actingAs($user)->put("/admin/applications/{$application->id}", [])->assertForbidden();
    }

    private function authorizedUser(): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('applications.update', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', 'applications.update']);

        return $user;
    }
}
