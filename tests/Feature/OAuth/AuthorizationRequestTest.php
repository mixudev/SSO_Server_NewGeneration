<?php

namespace Tests\Feature\OAuth;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationCredential;
use App\Models\Identity\ApplicationRedirectUri;
use App\Models\Identity\Organization;
use App\Models\Identity\Scope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorizationRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_valid_request_creates_pending_transaction(): void
    {
        $user = User::factory()->create();
        [$application, $credential] = $this->activeApplication();
        $scope = Scope::factory()->create(['name' => 'openid', 'status' => 'active']);
        $application->scopes()->attach($scope, ['allowed' => true, 'consent_required' => true]);
        ApplicationRedirectUri::factory()->create([
            'application_id' => $application->id,
            'uri' => 'https://client.example/callback',
            'uri_hash' => hash('sha256', 'https://client.example/callback'),
        ]);

        $response = $this->actingAs($user)->get(route('oauth.authorize', [
            'client_id' => $credential->passport_client_id,
            'redirect_uri' => 'https://client.example/callback',
            'response_type' => 'code',
            'scope' => 'openid',
            'state' => 'state-value',
            'code_challenge' => str_repeat('A', 43),
            'code_challenge_method' => 'S256',
            'nonce' => 'nonce-value',
        ]));

        $response->assertOk()->assertSee('Authorization consent required');
        $this->assertDatabaseHas('authorization_transactions', [
            'client_id' => $credential->passport_client_id,
            'application_id' => $application->id,
            'status' => 'pending',
            'response_type' => 'code',
            'nonce_hash' => hash('sha256', 'nonce-value'),
        ]);
    }

    public function test_unregistered_redirect_is_rejected_without_transaction(): void
    {
        $user = User::factory()->create();
        [$application, $credential] = $this->activeApplication();

        $response = $this->actingAs($user)->get(route('oauth.authorize', [
            'client_id' => $credential->passport_client_id,
            'redirect_uri' => 'https://evil.example/callback',
            'response_type' => 'code',
            'scope' => '',
            'state' => 'state-value',
            'code_challenge' => str_repeat('A', 43),
            'code_challenge_method' => 'S256',
        ]));

        $response->assertBadRequest()->assertSee('Invalid authorization request.');
        $this->assertDatabaseCount('authorization_transactions', 0);
    }

    /** @return array{0: Application, 1: ApplicationCredential} */
    private function activeApplication(): array
    {
        $organization = Organization::factory()->create(['status' => 'active']);
        $application = Application::factory()->for($organization)->create(['status' => 'active']);
        $credential = $application->credential()->create([
            'status' => 'active',
            'passport_client_id' => (string) Str::uuid(),
            'generation' => 1,
        ]);

        return [$application, $credential];
    }
}
