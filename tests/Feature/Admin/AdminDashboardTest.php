<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('security-defense.middleware.payload_scanner.enabled', false);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect();
    }

    public function test_authenticated_user_without_dashboard_permission_is_denied(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_dashboard_has_accessible_shell_and_safe_output(): void
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');

        $user = User::factory()->create(['name' => '<Admin>']);
        $user->givePermissionTo('admin.dashboard.view');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk()
            ->assertSee('Primary navigation', false)
            ->assertSee('<main', false)
            ->assertSee('Identity platform overview')
            ->assertSee('Security operations')
            ->assertDontSee('password')
            ->assertDontSee('client_secret')
            ->assertDontSee('private_key');
    }

    public function test_admin_asset_entrypoint_is_separate_from_authentication_asset_entrypoint(): void
    {
        $this->assertStringContainsString(
            'resources/css/dashboard.css',
            file_get_contents(resource_path('views/components/dashboard/layout.blade.php')) ?: '',
        );
        $this->assertStringNotContainsString(
            'resources/css/admin.css',
            file_get_contents(base_path('vendor/mixudev/laravel-authentication/resources/views/layouts/auth.blade.php')) ?: '',
        );
    }
}
