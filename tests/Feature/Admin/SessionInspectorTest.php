<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SessionInspectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_sees_active_sessions_without_payload(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['name' => 'Session User']);

        config(['session.driver' => 'database']);

        DB::table('sessions')->insert([
            'id' => 'session-visible',
            'user_id' => $user->id,
            'ip_address' => '192.0.2.44',
            'user_agent' => '<script>alert(1)</script>',
            'payload' => 'secret-cookie-payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)->get(route('admin.sessions.index'))
            ->assertOk()
            ->assertSee('Session User')
            ->assertSee('Currently connected identities')
            ->assertDontSee('secret-cookie-payload')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_user_without_session_permission_is_denied(): void
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.dashboard.view');

        $this->actingAs($admin)->get(route('admin.sessions.index'))->assertForbidden();
    }

    private function admin(): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('sessions.view', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', 'sessions.view']);

        return $user;
    }
}
