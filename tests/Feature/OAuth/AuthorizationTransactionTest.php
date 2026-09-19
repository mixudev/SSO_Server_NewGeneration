<?php

namespace Tests\Feature\OAuth;

use App\Domain\OAuth\Enums\AuthorizationTransactionStatus;
use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\OAuth\AuthorizationTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_persists_security_bindings_and_status(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $application = Application::factory()->for($organization)->create();

        $transaction = AuthorizationTransaction::query()->create([
            'transaction_id_hash' => hash('sha256', 'opaque-transaction'),
            'client_id' => '00000000-0000-0000-0000-000000000001',
            'application_id' => $application->id,
            'user_id' => $user->id,
            'redirect_uri_hash' => hash('sha256', 'https://client.test/callback'),
            'response_type' => 'code',
            'scope_string' => 'email profile',
            'state_hash' => hash('sha256', 'state'),
            'nonce_hash' => null,
            'code_challenge' => str_repeat('A', 43),
            'code_challenge_method' => 'S256',
            'status' => AuthorizationTransactionStatus::Pending,
            'expires_at' => now()->addMinute(),
        ]);

        $this->assertSame(AuthorizationTransactionStatus::Pending, $transaction->fresh()->status);
        $this->assertTrue($transaction->application->is($application));
        $this->assertTrue($transaction->user->is($user));
    }

    public function test_transaction_status_is_restricted_to_the_protocol_lifecycle(): void
    {
        $this->assertSame([
            'pending',
            'approved',
            'denied',
            'expired',
            'consumed',
        ], array_column(AuthorizationTransactionStatus::cases(), 'value'));
    }
}
