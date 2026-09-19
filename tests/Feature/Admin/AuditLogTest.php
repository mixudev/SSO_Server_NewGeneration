<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\SecurityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Mixudev\SecurityDefense\Models\SecurityDataAudit;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_requires_permission_and_redacts_safe_metadata(): void
    {
        $admin = $this->admin();
        SecurityEvent::query()->create([
            'event' => 'TEST_EVENT', 'risk' => 'high', 'actor' => 'actor-1', 'subject' => 'subject-1',
            'metadata' => ['safe' => '<script>alert(1)</script>', 'access_token' => 'secret-value'],
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.audit.index'));
        $response->assertOk()->assertSee('TEST_EVENT')->assertSee('[REDACTED]')->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('secret-value');

        $operator = User::factory()->create();
        $operator->givePermissionTo('admin.dashboard.view');
        $this->actingAs($operator)->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_audit_log_filters_and_caps_page_size(): void
    {
        $admin = $this->admin();
        SecurityEvent::query()->create(['event' => 'MATCH_EVENT', 'risk' => 'critical', 'occurred_at' => now(), 'metadata' => []]);
        SecurityEvent::query()->create(['event' => 'OTHER_EVENT', 'risk' => 'low', 'occurred_at' => now(), 'metadata' => []]);

        $this->actingAs($admin)->get(route('admin.audit.index', ['event' => 'MATCH_EVENT', 'risk' => 'critical', 'per_page' => 500]))
            ->assertSessionHasErrors('per_page');

        $this->actingAs($admin)->get(route('admin.audit.index', ['event' => 'MATCH_EVENT', 'risk' => 'critical', 'per_page' => 50]))
            ->assertOk()->assertSee('MATCH_EVENT')->assertDontSee('OTHER_EVENT');
    }

    public function test_security_retention_prunes_normal_and_tampered_records_by_different_windows(): void
    {
        $normal = SecurityDataAudit::query()->create([
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => 'normal-old',
            'created_at' => now()->subDays(31),
            'updated_at' => now()->subDays(31),
        ]);
        $tampered = SecurityDataAudit::query()->create([
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => 'tampered-old',
            'is_tampered' => true,
            'created_at' => now()->subDays(91),
            'updated_at' => now()->subDays(91),
        ]);

        Artisan::call('security-defense:prune');

        $this->assertDatabaseMissing('security_data_audits', ['id' => $normal->id]);
        $this->assertDatabaseMissing('security_data_audits', ['id' => $tampered->id]);
    }

    private function admin(): User
    {
        $role = Role::findOrCreate('audit_admin', 'web');
        $role->syncPermissions([
            Permission::findOrCreate('admin.dashboard.view', 'web'),
            Permission::findOrCreate('audit.view', 'web'),
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
