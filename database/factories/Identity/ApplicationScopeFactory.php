<?php

namespace Database\Factories\Identity;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationScope;
use App\Models\Identity\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationScope>
 */
class ApplicationScopeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'scope_id' => Scope::factory(),
            'allowed' => true,
            'consent_required' => true,
        ];
    }
}
