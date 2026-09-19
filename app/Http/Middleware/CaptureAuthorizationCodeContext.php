<?php

namespace App\Http\Middleware;

use App\Infrastructure\Passport\OidcTokenContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CaptureAuthorizationCodeContext
{
    public function __construct(private OidcTokenContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $code = $request->input('code');

        if (is_string($code) && $code !== '') {
            $this->context->setAuthorizationCodeHash(hash('sha256', $code));
        }

        return $next($request);
    }
}
