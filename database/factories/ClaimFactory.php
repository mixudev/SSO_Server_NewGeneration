<?php

namespace Database\Factories;

use App\Models\Claim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Claim>
 */
class ClaimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'user.email',
            'description' => fake()->sentence(),
            'source' => 'user.email',
            'sensitivity' => 'personal',
            'status' => 'active',
        ];
    }
}
