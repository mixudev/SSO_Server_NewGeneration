<?php

namespace Tests\Feature\OAuth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenErrorPathTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_authorization_code_returns_invalid_request_without_internal_details(): void
    {
        $response = $this->postJson(route('passport.token'), [
            'grant_type' => 'authorization_code',
            'client_id' => '00000000-0000-0000-0000-000000000001',
            'redirect_uri' => 'https://client.example/callback',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error', 'invalid_client')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }

    public function test_unknown_client_does_not_receive_a_token_or_internal_details(): void
    {
        $response = $this->postJson(route('passport.token'), [
            'grant_type' => 'authorization_code',
            'client_id' => '00000000-0000-0000-0000-000000000001',
            'code' => 'invalid-code',
            'redirect_uri' => 'https://client.example/callback',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error', 'invalid_client')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace')
            ->assertJsonMissingPath('access_token');
    }
}
