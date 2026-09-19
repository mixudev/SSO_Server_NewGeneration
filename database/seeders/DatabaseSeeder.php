<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * @var list<string>
     */
    private const PLATFORM_PERMISSIONS = [
        'admin.dashboard.view',
        'applications.view',
        'applications.create',
        'applications.update',
        'applications.delete',
        'applications.credentials.rotate',
        'applications.credentials.issue',
        'applications.access.view',
        'applications.access.manage',
        'users.view',
        'users.manage',
        'roles.view',
        'roles.manage',
        'organizations.view',
        'organizations.manage',
        'scopes.view',
        'scopes.manage',
        'claims.view',
        'claims.manage',
        'sessions.view',
        'sessions.revoke',
        'keys.view',
        'keys.rotate',
        'audit.view',
        'audit.export',
        'security.manage',
    ];

    public function run(): void
    {
        $permissions = collect(self::PLATFORM_PERMISSIONS)
            ->map(fn (string $name): Permission => Permission::findOrCreate($name, 'web'));

        $role = Role::findOrCreate('platform_admin', 'web');
        $role->syncPermissions($permissions);

        if (! app()->environment('local')) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        )->assignRole($role);

        $this->call(ExampleUserSeeder::class);
        $this->call(LocalDemoSeeder::class);
    }
}
