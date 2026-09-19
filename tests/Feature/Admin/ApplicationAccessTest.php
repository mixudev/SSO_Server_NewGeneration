<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationUserAccess;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ApplicationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_page_requires_view_permission(): void
    {
        [$admin, $application] = $this->fixture();
        $this->actingAs($admin)
            ->get(route('admin.applications.access.index', $application))
            ->assertForbidden();
    }

    public function test_admin_can_grant_and_revoke_user_access(): void
    {
        [$admin, $application, $user] = $this->fixture(['applications.access.manage']);

        $this->actingAs($admin)
            ->post(route('admin.applications.access.store', $application), ['user_id' => $user->getKey()])
            ->assertRedirect(route('admin.applications.access.index', $application));

        $this->assertDatabaseHas('application_user_access', [
            'application_id' => $application->getKey(),
            'user_id' => $user->getKey(),
            'status' => 'active',
        ]);

        $access = ApplicationUserAccess::query()->firstOrFail();
        $this->actingAs($admin)
            ->delete(route('admin.applications.access.destroy', [$application, $access]))
            ->assertRedirect(route('admin.applications.access.index', $application));

        $this->assertDatabaseHas('application_user_access', [
            'id' => $access->getKey(),
            'status' => 'revoked',
        ]);
    }

    public function test_duplicate_grant_is_rejected_without_duplicate_row(): void
    {
        [$admin, $application, $user] = $this->fixture(['applications.access.manage']);
        $application->userAccess()->create(['user_id' => $user->getKey(), 'status' => 'active']);

        $this->actingAs($admin)
            ->post(route('admin.applications.access.store', $application), ['user_id' => $user->getKey()])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('application_user_access', 1);
    }

    /** @param list<string> $permissions */
    private function fixture(array $permissions = []): array
    {
        $permissions[] = 'admin.dashboard.view';
        $role = Role::findOrCreate('application_access_admin', 'web');
        $role->syncPermissions(collect($permissions)->map(fn (string $name): Permission => Permission::findOrCreate($name, 'web')));
        $admin = User::factory()->create(['active' => true, 'status' => 'active']);
        $admin->assignRole($role);
        $application = Application::factory()->create([
            'status' => 'active',
            'organization_id' => Organization::factory()->create(['status' => 'active'])->getKey(),
        ]);
        $user = User::factory()->create(['active' => true, 'status' => 'active']);

        return [$admin, $application, $user];
    }
}
