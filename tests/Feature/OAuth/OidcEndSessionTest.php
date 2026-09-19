<?php

namespace Tests\Feature\OAuth;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationCredential;
use App\Models\Identity\ApplicationRedirectUri;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Tests\TestCase;

class OidcEndSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_post_logout_redirect_is_allowed_and_state_is_preserved(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => 'active',
            'protocol_mode' => 'oidc',
            'client_type' => 'public_spa',
            'organization_id' => Organization::factory()->create(['status' => 'active'])->getKey(),
        ]);
        $client = Client::factory()->create(['revoked' => false]);
        ApplicationCredential::query()->create([
            'application_id' => $application->getKey(),
            'passport_client_id' => $client->getKey(),
            'status' => 'active',
            'generation' => 1,
        ]);
        ApplicationRedirectUri::factory()->create([
            'application_id' => $application->getKey(),
            'kind' => 'logout',
            'uri' => 'https://client.example/logout/callback',
            'uri_hash' => hash('sha256', 'https://client.example/logout/callback'),
        ]);

        $this->actingAs($user)
            ->get(route('oidc.end-session', [
                'client_id' => $client->getKey(),
                'post_logout_redirect_uri' => 'https://client.example/logout/callback',
                'state' => 'logout-state',
            ]))
            ->assertRedirect('https://client.example/logout/callback?state=logout-state');
    }

    public function test_unregistered_post_logout_redirect_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('oidc.end-session', [
                'client_id' => 'unknown-client',
                'post_logout_redirect_uri' => 'https://evil.example/callback',
            ]))
            ->assertStatus(400);
    }
}
