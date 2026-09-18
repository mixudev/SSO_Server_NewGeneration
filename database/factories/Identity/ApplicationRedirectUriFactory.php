<?php

namespace Database\Factories\Identity;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationRedirectUri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationRedirectUri>
 */
class ApplicationRedirectUriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uri = 'https://'.fake()->domainName().'/oauth/callback';

        return [
            'application_id' => Application::factory(),
            'uri' => $uri,
            'uri_hash' => hash('sha256', $uri),
            'kind' => 'login',
        ];
    }
}
