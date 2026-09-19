<?php

namespace Tests\Feature\OAuth;

use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\OAuth\AuthorizationTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentStateBindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_consent_rejects_state_that_does_not_match_transaction_hash(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['status' => 'active']);
        $application = Application::factory()->for($organization)->create(['status' => 'active']);
        $transaction = AuthorizationTransaction::query()->create([
            'transaction_id_hash' => hash('sha256', 'transaction-id'),
            'client_id' => 'client-id',
            'application_id' => $application->getKey(),
            'user_id' => $user->getKey(),
            'redirect_uri_hash' => hash('sha256', 'https://client.example/callback'),
            'response_type' => 'code',
            'scope_string' => 'openid',
            'state_hash' => hash('sha256', 'expected-state'),
            'code_challenge' => str_repeat('a', 43),
            'code_challenge_method' => 'S256',
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->actingAs($user)->withSession([
            'oauth.authorization.'.$transaction->getKey() => [
                'redirect_uri' => 'https://client.example/callback',
                'state' => 'attacker-state',
            ],
        ])->post(route('oauth.consent.approve', $transaction))
            ->assertBadRequest();

        $this->assertSame('pending', $transaction->fresh()->status->value);
    }
}
