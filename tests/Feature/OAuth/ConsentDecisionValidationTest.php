<?php

namespace Tests\Feature\OAuth;

use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\OAuth\AuthorizationTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConsentDecisionValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_consent_decision_is_rejected_without_issuing_code(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for(Organization::factory()->create(['status' => 'active']))->create(['status' => 'active']);
        $transaction = AuthorizationTransaction::query()->create([
            'transaction_id_hash' => hash('sha256', (string) Str::ulid()),
            'client_id' => (string) Str::uuid(),
            'application_id' => $application->getKey(),
            'user_id' => $user->getKey(),
            'redirect_uri_hash' => hash('sha256', 'https://client.example/callback'),
            'response_type' => 'code',
            'scope_string' => 'openid',
            'state_hash' => hash('sha256', 'state-value'),
            'code_challenge' => str_repeat('A', 43),
            'code_challenge_method' => 'S256',
            'status' => 'pending',
            'expires_at' => now()->addMinute(),
        ]);

        $this->actingAs($user)->withSession([
            'oauth.authorization.'.$transaction->getKey() => [
                'redirect_uri' => 'https://client.example/callback',
                'state' => 'state-value',
            ],
        ])->post(route('oauth.consent.approve', $transaction), ['decision' => 'unexpected'])
            ->assertBadRequest();

        $this->assertSame('pending', $transaction->fresh()->status->value);
        $this->assertDatabaseCount('oauth_auth_codes', 0);
    }
}
