<?php

namespace Database\Factories\Identity;

use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'protocol_mode' => 'oidc',
            'client_type' => 'confidential_web',
            'status' => 'draft',
            'description' => fake()->sentence(),
            'homepage_url' => 'https://'.fake()->domainName(),
            'privacy_url' => null,
            'terms_url' => null,
            'consent_policy' => 'explicit',
            'session_policy_json' => [],
            'claim_policy_version' => 1,
        ];
    }
}
