<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationScope;
use App\Models\Scope;
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
