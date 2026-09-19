<?php

namespace Tests\Feature\OAuth;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Domain\Oidc\Services\IdTokenVerifier;
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

class TokenExchangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pkce_code_can_be_exchanged_once_for_tokens(): void
    {
        [$user, $credential, $verifier] = $this->approveCode();
        $transaction = AuthorizationTransaction::query()->firstOrFail();
        $location = $this->actingAs($user)->withSession([
            'oauth.authorization.'.$transaction->getKey() => [
                'redirect_uri' => 'https://client.example/callback',
                'state' => 'state-value',
            ],
        ])->post(route('oauth.consent.approve', $transaction))->headers->get('Location');
        $code = (string) (parse_url($location, PHP_URL_QUERY) ? Str::of(parse_url($location, PHP_URL_QUERY))->after('code=')->before('&')->toString() : '');

        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $credential->passport_client_id,
            'redirect_uri' => 'https://client.example/callback',
            'code' => urldecode($code),
            'code_verifier' => $verifier,
        ]);

        $response->assertOk()->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token', 'id_token']);
        $payload = app(IdTokenVerifier::class)->verify(
            token: $response->json('id_token'),
            key: app(KeyManagerInterface::class)->active(),
            issuer: config('app.url'),
            audience: $credential->passport_client_id,
            nonce: 'nonce-value',
            now: now()->timestamp,
        );
        $this->assertSame((string) $user->uuid, $payload['sub']);
        $this->assertSame('nonce-value', $payload['nonce']);
        $this->assertDatabaseHas('oauth_auth_codes', [
            'revoked' => true,
        ]);

        $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $credential->passport_client_id,
            'redirect_uri' => 'https://client.example/callback',
            'code' => urldecode($code),
            'code_verifier' => $verifier,
        ])->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
    }

    public function test_wrong_pkce_verifier_is_rejected_and_code_is_not_issued(): void
    {
        [$user, $credential] = $this->approveCode();
        $transaction = AuthorizationTransaction::query()->firstOrFail();
        $location = $this->approve($user, $transaction)->headers->get('Location');
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $credential->passport_client_id,
            'redirect_uri' => 'https://client.example/callback',
            'code' => $query['code'],
            'code_verifier' => str_repeat('b', 43),
        ])->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
    }

    /** @return array{0: User, 1: ApplicationCredential, 2: string} */
    private function approveCode(): array
    {
        $user = User::factory()->create();
        app(KeyManagerInterface::class)->generate();
        [$application, $credential] = $this->activeApplication();
        $verifier = 'verifier-value-abcdefghijklmnopqrstuvwxyz-123456789';
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $scopes = Scope::factory()->createMany([
            ['name' => 'openid', 'status' => 'active'],
            ['name' => 'email', 'status' => 'active'],
        ]);
        $application->scopes()->attach($scopes->modelKeys(), ['allowed' => true, 'consent_required' => true]);
        ApplicationRedirectUri::factory()->create([
            'application_id' => $application->id,
            'uri' => 'https://client.example/callback',
            'uri_hash' => hash('sha256', 'https://client.example/callback'),
        ]);

        $this->actingAs($user)->get(route('oauth.authorize', [
            'client_id' => $credential->passport_client_id,
            'redirect_uri' => 'https://client.example/callback',
            'response_type' => 'code',
            'scope' => 'openid email',
            'state' => 'state-value',
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
            'nonce' => 'nonce-value',
        ]));

        return [$user, $credential, $verifier];
    }

    private function approve(User $user, AuthorizationTransaction $transaction): TestResponse
    {
        return $this->actingAs($user)->withSession([
            'oauth.authorization.'.$transaction->getKey() => [
                'redirect_uri' => 'https://client.example/callback',
                'state' => 'state-value',
            ],
        ])->post(route('oauth.consent.approve', $transaction));
    }

    /** @return array{0: Application, 1: ApplicationCredential} */
    private function activeApplication(): array
    {
        $organization = Organization::factory()->create(['status' => 'active']);
        $application = Application::factory()->for($organization)->create(['status' => 'active']);
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            $application->name,
            ['https://client.example/callback'],
            false,
        );
        $credential = $application->credential()->create([
            'status' => 'active',
            'passport_client_id' => (string) $client->getKey(),
            'generation' => 1,
        ]);

        return [$application, $credential];
    }
}
