<?php

namespace Tests\Feature;

use Tests\TestCase;

class OidcDiscoveryTest extends TestCase
{
    public function test_openid_configuration_exposes_provider_endpoints(): void
    {
        $this->getJson('/.well-known/openid-configuration')
            ->assertOk()
            ->assertJsonPath('issuer', config('app.url'))
            ->assertJsonPath('authorization_endpoint', config('app.url').'/oauth/authorize')
            ->assertJsonPath('token_endpoint', config('app.url').'/oauth/token')
            ->assertJsonPath('userinfo_endpoint', config('app.url').'/oauth/userinfo')
            ->assertJsonPath('jwks_uri', config('app.url').'/.well-known/jwks.json')
            ->assertJsonPath('code_challenge_methods_supported.0', 'S256');
    }
}
