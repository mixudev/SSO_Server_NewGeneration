<?php

namespace App\Http\Controllers\Oidc;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class DiscoveryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $issuer = rtrim((string) config('app.url'), '/');

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.'/oauth/authorize',
            'token_endpoint' => $issuer.'/oauth/token',
            'userinfo_endpoint' => $issuer.'/oauth/userinfo',
            'end_session_endpoint' => $issuer.'/oauth/end-session',
            'revocation_endpoint' => $issuer.'/oauth/revoke',
            'jwks_uri' => $issuer.'/.well-known/jwks.json',
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'profile', 'email'],
            'code_challenge_methods_supported' => ['S256'],
        ]);
    }
}
