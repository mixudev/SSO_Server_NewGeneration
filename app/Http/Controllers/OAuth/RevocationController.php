<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

final class RevocationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->string('token')->toString();
        $tokenId = $this->validatedTokenId($request, $token);

        if ($tokenId !== null) {
            Passport::token()->newQuery()
                ->whereKey($tokenId)
                ->update(['revoked' => true]);

            Passport::refreshToken()->newQuery()
                ->where('access_token_id', $tokenId)
                ->update(['revoked' => true]);
        }

        return response()->json(['revoked' => true]);
    }

    private function validatedTokenId(Request $request, string $token): ?string
    {
        if ($token === '') {
            return null;
        }

        $request->headers->set('Authorization', 'Bearer '.$token);

        try {
            $psrRequest = (new PsrHttpFactory)->createRequest($request);
            $validatedRequest = app(ResourceServer::class)->validateAuthenticatedRequest($psrRequest);

            $tokenId = $validatedRequest->getAttribute('oauth_access_token_id');

            return is_string($tokenId) && $tokenId !== '' ? $tokenId : null;
        } catch (\Throwable) {
            return null;
        } finally {
            $request->headers->remove('Authorization');
        }
    }
}
