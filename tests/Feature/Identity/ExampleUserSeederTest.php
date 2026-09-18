<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ExampleUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_local_example_admin_login(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'lazamediamxt@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertTrue($user->hasRole('platform_admin'));
        $this->assertEqualsCanonicalizing(
            Permission::query()->pluck('name')->all(),
            $user->getAllPermissions()->pluck('name')->all(),
        );
    }

    public function test_example_user_seeder_is_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(1, User::query()->where('email', 'lazamediamxt@gmail.com')->count());
    }
}
