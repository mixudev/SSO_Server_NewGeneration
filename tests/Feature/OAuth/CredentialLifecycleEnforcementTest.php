<?php

namespace Tests\Feature\OAuth;

use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class CredentialLifecycleEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_revoked_application_credential_cannot_start_authorization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['status' => 'active']);
        $application = Application::factory()->for($organization)->create(['status' => 'active']);
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            $application->name,
            ['https://client.example/callback'],
            false,
        );
        $credential = $application->credential()->create([
            'passport_client_id' => $client->getKey(),
            'status' => 'revoked',
            'generation' => 1,
        ]);

        $this->assertSame('revoked', $credential->status);

        $this->actingAs($user)
            ->get(route('oauth.authorize', [
                'client_id' => $client->getKey(),
                'redirect_uri' => 'https://client.example/callback',
                'response_type' => 'code',
                'scope' => 'openid',
                'state' => 'state-value',
                'code_challenge' => str_repeat('a', 43),
                'code_challenge_method' => 'S256',
                'nonce' => 'nonce-value',
            ]))
            ->assertStatus(400);
    }
}
