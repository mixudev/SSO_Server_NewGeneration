<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ExampleUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => 'lazamediamxt@gmail.com'],
            [
                'name' => 'Lazamediamxt Admin',
                'password' => env('EXAMPLE_USER_PASSWORD', 'password'),
                'email_verified_at' => now(),
            ],
        );

        $user->assignRole('platform_admin');
    }
}
