<?php

namespace App\Infrastructure\OAuth;

use App\Models\OAuth\AuthorizationTransaction;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\Bridge\User;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

final class PassportAuthorizationCodeIssuer
{
    public function __construct(
        private AuthorizationServer $server,
        private ClientRepository $clients,
    ) {}

    public function issue(AuthorizationTransaction $transaction, string $redirectUri, string $state): Response
    {
        $client = $this->clients->getClientEntity($transaction->client_id);
        if ($client === null) {
            throw new \InvalidArgumentException('OAuth client is unavailable.');
        }

        $authorization = new AuthorizationRequest;
        $authorization->setGrantTypeId('authorization_code');
        $authorization->setClient($client);
        $authorization->setUser(new User((string) $transaction->user_id));
        $authorization->setRedirectUri($redirectUri);
        $authorization->setState($state);
        $authorization->setAuthorizationApproved(true);
        $authorization->setCodeChallenge($transaction->code_challenge);
        $authorization->setCodeChallengeMethod($transaction->code_challenge_method);
        $authorization->setScopes(array_map(
            static fn (string $scope): Scope => new Scope($scope),
            preg_split('/\s+/', trim($transaction->scope_string), -1, PREG_SPLIT_NO_EMPTY),
        ));

        $psrResponse = $this->server->completeAuthorizationRequest(
            $authorization,
            (new PsrHttpFactory)->createResponse(new Response),
        );

        $response = (new HttpFoundationFactory)->createResponse($psrResponse);
        $location = $response->headers->get('Location');

        if (is_string($location)) {
            parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
            $code = $query['code'] ?? null;

            if (is_string($code) && $code !== '') {
                $transaction->forceFill([
                    'authorization_code_hash' => hash('sha256', $code),
                ])->save();
            }
        }

        return $response;
    }
}
