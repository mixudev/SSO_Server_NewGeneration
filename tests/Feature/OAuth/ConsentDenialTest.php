<?php

namespace Tests\Feature\OAuth;

use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\OAuth\AuthorizationTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConsentDenialTest extends TestCase
{
    use RefreshDatabase;

    public function test_denial_marks_transaction_and_redirects_protocol_error_without_code(): void
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
            'code_challenge' => str_repeat('A', 43),
            'code_challenge_method' => 'S256',
            'state_hash' => hash('sha256', 'state-value'),
            'status' => 'pending',
            'expires_at' => now()->addMinute(),
        ]);

        $response = $this->actingAs($user)->withSession([
            'oauth.authorization.'.$transaction->getKey() => [
                'redirect_uri' => 'https://client.example/callback',
                'state' => 'state-value',
            ],
        ])->post(route('oauth.consent.approve', $transaction), ['decision' => 'deny']);

        $response->assertRedirect('https://client.example/callback?error=access_denied&state=state-value');
        $this->assertSame('denied', $transaction->fresh()->status->value);
        $this->assertDatabaseCount('oauth_auth_codes', 0);
    }
}
