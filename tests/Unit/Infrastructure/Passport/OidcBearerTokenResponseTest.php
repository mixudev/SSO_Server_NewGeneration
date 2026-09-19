<?php

namespace Tests\Unit\Infrastructure\Passport;

use App\Infrastructure\Passport\OidcBearerTokenResponse;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use PHPUnit\Framework\TestCase;

class OidcBearerTokenResponseTest extends TestCase
{
    public function test_response_type_is_available_for_oidc_token_response_extension(): void
    {
        $response = new OidcBearerTokenResponse;

        $this->assertInstanceOf(BearerTokenResponse::class, $response);
    }
}
