<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\SigningKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KeyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_key_page_requires_security_permission_and_hides_private_material(): void
    {
        $key = (new SigningKey)->forceFill([
            'kid' => 'kid-test', 'algorithm' => 'RS256', 'public_key' => 'PUBLIC', 'private_key' => 'PRIVATE',
            'status' => 'active', 'active_slot' => 1, 'activated_at' => now(),
        ]);
        $key->save();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.keys.index'))
            ->assertOk()->assertSee($key->kid)->assertSee('RS256')->assertDontSee('PRIVATE');

        $operator = User::factory()->create();
        $operator->givePermissionTo('admin.dashboard.view');
        $this->actingAs($operator)->get(route('admin.keys.index'))->assertForbidden();
    }

    public function test_rotation_retires_old_key_and_audit_excludes_private_material(): void
    {
        $admin = $this->admin();
        $old = (new SigningKey)->forceFill([
            'kid' => 'kid-old', 'algorithm' => 'RS256', 'public_key' => 'PUBLIC', 'private_key' => 'PRIVATE',
            'status' => 'active', 'active_slot' => 1, 'activated_at' => now(),
        ]);
        $old->save();

        $this->actingAs($admin)->post(route('admin.keys.rotate'))
            ->assertRedirect(route('admin.keys.index'));

        $this->assertSame('retired', $old->refresh()->status);
        $this->assertSame(1, SigningKey::query()->where('status', 'active')->count());
        $this->assertDatabaseHas('security_events', ['event' => 'SIGNING_KEY_ROTATED']);
        $this->assertDatabaseMissing('security_events', ['metadata->private_key' => 'PRIVATE']);
    }

    public function test_last_active_key_cannot_be_retired(): void
    {
        $admin = $this->admin();
        $key = (new SigningKey)->forceFill([
            'kid' => 'kid-only', 'algorithm' => 'RS256', 'public_key' => 'PUBLIC', 'private_key' => 'PRIVATE',
            'status' => 'active', 'active_slot' => 1, 'activated_at' => now(),
        ]);
        $key->save();

        $this->actingAs($admin)->delete(route('admin.keys.revoke', $key))->assertStatus(422);
        $this->assertSame('active', $key->refresh()->status);
    }

    private function admin(): User
    {
        $role = Role::findOrCreate('key_admin', 'web');
        $role->syncPermissions([
            Permission::findOrCreate('admin.dashboard.view', 'web'),
            Permission::findOrCreate('keys.view', 'web'),
            Permission::findOrCreate('keys.rotate', 'web'),
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
