<?php

namespace Tests\Feature\OAuth;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationRedirectUri;
use App\Models\Identity\Organization;
use App\Models\Identity\Scope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OidcNonceBindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_oidc_authorization_requires_nonce(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['status' => 'active']);
        $application = Application::factory()->for($organization)->create(['status' => 'active', 'protocol_mode' => 'oidc']);
        $credential = $application->credential()->create(['status' => 'active', 'passport_client_id' => (string) Str::uuid(), 'generation' => 1]);
        $scope = Scope::factory()->create(['name' => 'openid', 'status' => 'active']);
        $application->scopes()->attach($scope, ['allowed' => true, 'consent_required' => true]);
        ApplicationRedirectUri::factory()->create(['application_id' => $application->id, 'uri' => 'https://client.example/callback', 'uri_hash' => hash('sha256', 'https://client.example/callback')]);

        $this->actingAs($user)->get(route('oauth.authorize', [
            'client_id' => $credential->passport_client_id,
            'redirect_uri' => 'https://client.example/callback',
            'response_type' => 'code',
            'scope' => 'openid',
            'state' => 'state-value',
            'code_challenge' => str_repeat('A', 43),
            'code_challenge_method' => 'S256',
        ]))->assertBadRequest();

        $this->assertDatabaseCount('authorization_transactions', 0);
    }
}
