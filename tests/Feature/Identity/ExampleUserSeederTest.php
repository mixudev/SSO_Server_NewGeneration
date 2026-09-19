<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use Database\Seeders\LocalDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ExampleUserSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->detectEnvironment(fn (): string => 'local');
    }

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

    public function test_database_seeder_does_not_create_dummy_users_outside_local(): void
    {
        app()->detectEnvironment(fn (): string => 'production');

        (new LocalDemoSeeder)->run();

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'lazamediamxt@gmail.com']);
    }
}
