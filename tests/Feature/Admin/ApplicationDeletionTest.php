<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApplicationDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('security-defense.middleware.payload_scanner.enabled', false);
    }

    public function test_authorized_user_can_delete_draft_and_audit_event_is_written(): void
    {
        $user = $this->authorizedUser();
        $application = Application::factory()->create(['status' => 'draft']);

        $this->actingAs($user)->delete("/admin/applications/{$application->id}", ['current_password' => 'password'])
            ->assertRedirect(route('admin.applications.index'));

        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
        $this->assertDatabaseHas('security_events', [
            'event' => 'APPLICATION_DELETED',
            'application_id' => $application->id,
            'actor' => (string) $user->id,
        ]);
    }

    public function test_active_application_cannot_be_deleted_directly(): void
    {
        $user = $this->authorizedUser();
        $application = Application::factory()->create(['status' => 'active']);

        $this->actingAs($user)->delete("/admin/applications/{$application->id}", ['current_password' => 'password'])
            ->assertSessionHasErrors('application');

        $this->assertDatabaseHas('applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('security_events', ['event' => 'APPLICATION_DELETED']);
    }

    public function test_user_without_delete_permission_is_denied(): void
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.dashboard.view');
        $application = Application::factory()->create(['status' => 'draft']);

        $this->actingAs($user)->delete("/admin/applications/{$application->id}")->assertForbidden();
    }

    private function authorizedUser(): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('applications.delete', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', 'applications.delete']);

        return $user;
    }
}
