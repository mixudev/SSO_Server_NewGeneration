<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApplicationCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('security-defense.middleware.payload_scanner.enabled', false);
    }

    public function test_user_without_create_permission_cannot_create_application(): void
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.dashboard.view');

        $this->actingAs($user)->post('/admin/applications', [])->assertForbidden();
    }

    public function test_authorized_user_can_create_application_with_canonical_redirect_uri(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();

        $response = $this->actingAs($user)->post('/admin/applications', [
            'organization_id' => $organization->id,
            'name' => 'Portal',
            'slug' => 'portal',
            'protocol_mode' => 'oidc',
            'client_type' => 'confidential_web',
            'description' => 'Portal app',
            'redirect_uris' => ['HTTPS://CLIENT.EXAMPLE/callback'],
        ]);

        $response->assertRedirect(route('admin.applications.index'));
        $application = Application::query()->where('slug', 'portal')->firstOrFail();
        $this->assertSame('https://client.example/callback', $application->redirectUris()->firstOrFail()->uri);
        $this->assertSame('draft', $application->status);
    }

    public function test_invalid_redirect_uri_is_rejected_without_persisting_application(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();

        $this->actingAs($user)->from('/admin/applications/create')->post('/admin/applications', [
            'organization_id' => $organization->id,
            'name' => 'Unsafe',
            'slug' => 'unsafe',
            'protocol_mode' => 'oidc',
            'client_type' => 'confidential_web',
            'redirect_uris' => ['https://client.example/callback#fragment'],
        ])->assertSessionHasErrors('redirect_uris.0');

        $this->assertDatabaseMissing('applications', ['slug' => 'unsafe']);
    }

    private function authorizedUser(): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('applications.create', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', 'applications.create']);

        return $user;
    }
}
