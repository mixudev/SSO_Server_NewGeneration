<?php

namespace Database\Factories;

use App\Models\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scope>
 */
class ScopeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'account:read',
            'description' => fake()->sentence(),
            'category' => 'custom',
            'risk_level' => 'low',
            'is_system' => false,
            'is_default' => false,
            'status' => 'active',
        ];
    }
}
