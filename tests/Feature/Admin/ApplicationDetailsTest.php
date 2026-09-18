<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationRedirectUri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApplicationDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('security-defense.middleware.payload_scanner.enabled', false);
    }

    public function test_authorized_user_can_view_application_details_without_secret_material(): void
    {
        $user = $this->authorizedUser();
        $application = Application::factory()->create([
            'name' => 'Customer Portal',
            'description' => 'Production identity client',
        ]);
        ApplicationRedirectUri::factory()->create([
            'application_id' => $application->id,
            'uri' => 'https://client.example/callback',
        ]);

        $this->actingAs($user)->get("/admin/applications/{$application->id}")
            ->assertOk()
            ->assertViewIs('pages.admin.applications.show')
            ->assertSee('Customer Portal')
            ->assertSee('https://client.example/callback')
            ->assertDontSee('private_key')
            ->assertDontSee('client_secret');
    }

    public function test_user_without_view_permission_is_denied(): void
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.dashboard.view');
        $application = Application::factory()->create();

        $this->actingAs($user)->get("/admin/applications/{$application->id}")->assertForbidden();
    }

    private function authorizedUser(): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('applications.view', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', 'applications.view']);

        return $user;
    }
}
