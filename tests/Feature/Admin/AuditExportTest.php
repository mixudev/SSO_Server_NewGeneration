<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\SecurityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_export_returns_bounded_redacted_csv(): void
    {
        $admin = $this->admin('audit.export');
        SecurityEvent::query()->create([
            'event' => 'EXPORT_TEST',
            'risk' => 'high',
            'actor' => 'actor-1',
            'subject' => 'subject-1',
            'metadata' => ['safe' => 'visible', 'access_token' => 'never-export'],
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.audit.export', ['event' => 'EXPORT_TEST']));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertSee('EXPORT_TEST')
            ->assertSee('[REDACTED]')
            ->assertDontSee('never-export');
    }

    public function test_audit_export_requires_separate_permission(): void
    {
        $admin = $this->admin('audit.view');

        $this->actingAs($admin)->get(route('admin.audit.export'))->assertForbidden();
    }

    private function admin(string $permission): User
    {
        foreach (['admin.dashboard.view', 'audit.view', 'audit.export'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $role = Role::findOrCreate('audit_exporter', 'web');
        $role->syncPermissions([$permission, 'admin.dashboard.view']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
