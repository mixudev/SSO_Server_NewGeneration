<?php

namespace Tests\Feature\OAuth;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationCredential;
use App\Models\Identity\ApplicationRedirectUri;
use App\Models\Identity\Organization;
use App\Models\Identity\Scope;
use App\Models\OAuth\AuthorizationTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class AuthorizationConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_issues_one_time_code_and_redirects_with_state(): void
    {
        $user = User::factory()->create();
        [$application, $credential] = $this->activeApplication();
        $scope = Scope::factory()->create(['name' => 'email', 'status' => 'active']);
        $application->scopes()->attach($scope, ['allowed' => true, 'consent_required' => true]);
        ApplicationRedirectUri::factory()->create([
            'application_id' => $application->id,
            'uri' => 'https://client.example/callback',
            'uri_hash' => hash('sha256', 'https://client.example/callback'),
        ]);

        $this->actingAs($user)->get(route('oauth.authorize', [
            'client_id' => $credential->passport_client_id,
            'redirect_uri' => 'https://client.example/callback',
            'response_type' => 'code',
            'scope' => 'email',
            'state' => 'state-value',
            'code_challenge' => str_repeat('A', 43),
            'code_challenge_method' => 'S256',
        ]));
        $transaction = AuthorizationTransaction::query()->firstOrFail();

        $response = $this->approve($user, $transaction);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://client.example/callback?', $location);
        $this->assertStringContainsString('state=state-value', $location);
        $this->assertDatabaseHas('oauth_auth_codes', [
            'client_id' => $credential->passport_client_id,
            'revoked' => false,
        ]);
        $this->assertSame('approved', $transaction->fresh()->status->value);
    }

    public function test_approval_cannot_be_replayed(): void
    {
        $user = User::factory()->create();
        $transaction = $this->pendingTransaction($user);

        $this->approve($user, $transaction)->assertRedirect();
        $this->actingAs($user)->withSession([
            'oauth.authorization.'.$transaction->getKey() => [
                'redirect_uri' => 'https://client.example/callback',
                'state' => 'state-value',
            ],
        ])->post(route('oauth.consent.approve', $transaction))->assertBadRequest();

        $this->assertDatabaseCount('oauth_auth_codes', 1);
    }

    public function test_another_user_cannot_approve_transaction(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $transaction = $this->pendingTransaction($owner);

        $this->actingAs($attacker)->withSession([
            'oauth.authorization.'.$transaction->getKey() => [
                'redirect_uri' => 'https://client.example/callback',
                'state' => 'state-value',
            ],
        ])->post(route('oauth.consent.approve', $transaction))->assertBadRequest();

        $this->assertDatabaseCount('oauth_auth_codes', 0);
        $this->assertSame('pending', $transaction->fresh()->status->value);
    }

    public function test_expired_transaction_cannot_be_approved(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['status' => 'active']);
        $application = Application::factory()->for($organization)->create(['status' => 'active', 'protocol_mode' => 'oauth2']);
        $transaction = AuthorizationTransaction::query()->create([
            'transaction_id_hash' => hash('sha256', 'expired-transaction'),
            'client_id' => (string) Str::uuid(),
            'application_id' => $application->id,
            'user_id' => $user->id,
            'redirect_uri_hash' => hash('sha256', 'https://client.example/callback'),
            'response_type' => 'code',
            'scope_string' => 'email',
            'state_hash' => hash('sha256', 'state-value'),
            'code_challenge' => str_repeat('A', 43),
            'code_challenge_method' => 'S256',
            'status' => 'pending',
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($user)->withSession([
            'oauth.authorization.'.$transaction->getKey() => [
                'redirect_uri' => 'https://client.example/callback',
                'state' => 'state-value',
            ],
        ])->post(route('oauth.consent.approve', $transaction), [
            'decision' => 'approve',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('oauth_auth_codes', 0);
        $this->assertSame('expired', $transaction->fresh()->status->value);
    }

    private function approve(User $user, AuthorizationTransaction $transaction): TestResponse
    {
        return $this->actingAs($user)->withSession([
            'oauth.authorization.'.$transaction->getKey() => [
                'redirect_uri' => 'https://client.example/callback',
                'state' => 'state-value',
            ],
        ])->post(route('oauth.consent.approve', $transaction), [
            'decision' => 'approve',
        ]);
    }

    private function pendingTransaction(User $user): AuthorizationTransaction
    {
        $organization = Organization::factory()->create(['status' => 'active']);
        $application = Application::factory()->for($organization)->create(['status' => 'active', 'protocol_mode' => 'oauth2']);
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            $application->name,
            ['https://client.example/callback'],
            false,
        );
        $application->credential()->create([
            'status' => 'active',
            'passport_client_id' => (string) $client->getKey(),
            'generation' => 1,
        ]);

        return AuthorizationTransaction::query()->create([
            'transaction_id_hash' => hash('sha256', (string) Str::ulid()),
            'client_id' => (string) $client->getKey(),
            'application_id' => $application->id,
            'user_id' => $user->id,
            'redirect_uri_hash' => hash('sha256', 'https://client.example/callback'),
            'response_type' => 'code',
            'scope_string' => 'email',
            'state_hash' => hash('sha256', 'state-value'),
            'code_challenge' => str_repeat('A', 43),
            'code_challenge_method' => 'S256',
            'status' => 'pending',
            'expires_at' => now()->addMinute(),
        ]);
    }

    /** @return array{0: Application, 1: ApplicationCredential} */
    private function activeApplication(): array
    {
        $organization = Organization::factory()->create(['status' => 'active']);
        $application = Application::factory()->for($organization)->create(['status' => 'active', 'protocol_mode' => 'oauth2']);
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            $application->name,
            ['https://client.example/callback'],
            $application->client_type === 'confidential_web',
        );
        $credential = $application->credential()->create([
            'status' => 'active',
            'passport_client_id' => (string) $client->getKey(),
            'generation' => 1,
        ]);

        return [$application, $credential];
    }
}
