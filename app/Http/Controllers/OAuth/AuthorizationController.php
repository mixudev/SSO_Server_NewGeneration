<?php

namespace App\Http\Controllers\OAuth;

use App\Domain\OAuth\Data\AuthorizationRequestData;
use App\Domain\OAuth\Data\OAuthClientData;
use App\Domain\OAuth\Enums\AuthorizationTransactionStatus;
use App\Domain\OAuth\Services\AuthorizationRequestValidator;
use App\Http\Controllers\Controller;
use App\Models\Identity\ApplicationCredential;
use App\Models\OAuth\AuthorizationTransaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class AuthorizationController extends Controller
{
    public function __construct(private AuthorizationRequestValidator $validator) {}

    public function authorize(Request $request): Response
    {
        try {
            $authorizationRequest = AuthorizationRequestData::fromArray($request->query());
            $credential = ApplicationCredential::query()
                ->with(['application.organization', 'application.redirectUris', 'application.scopes'])
                ->where('passport_client_id', $authorizationRequest->clientId)
                ->where('status', 'active')
                ->firstOrFail();
            $application = $credential->application;
            if ($application->protocol_mode === 'oidc'
                && ($authorizationRequest->nonce === null || $authorizationRequest->nonce === '')
            ) {
                throw new InvalidArgumentException('OIDC authorization requires a nonce.');
            }

            if ($application->protocol_mode === 'oidc'
                && ! in_array('openid', preg_split('/\s+/', trim($authorizationRequest->scope), -1, PREG_SPLIT_NO_EMPTY), true)
            ) {
                throw new InvalidArgumentException('OIDC authorization requires the openid scope.');
            }

            $registeredScopes = $application->scopes->mapWithKeys(fn ($scope): array => [
                $scope->name => [
                    'allowed' => (bool) $scope->pivot->allowed,
                    'status' => (string) $scope->status,
                ],
            ])->all();
            $allowedScopes = $this->validator->validate(
                $authorizationRequest,
                new OAuthClientData(
                    $credential->passport_client_id,
                    null,
                    $application->client_type === 'confidential_web',
                    false,
                ),
                $application->status === 'active',
                $application->organization->status === 'active',
                $application->redirectUris->pluck('uri')->all(),
                $registeredScopes,
            );

            $transactionId = (string) Str::ulid();
            $transaction = AuthorizationTransaction::query()->create([
                'transaction_id_hash' => hash('sha256', $transactionId),
                'client_id' => $authorizationRequest->clientId,
                'application_id' => $application->getKey(),
                'user_id' => $request->user()->getAuthIdentifier(),
                'redirect_uri_hash' => hash('sha256', $authorizationRequest->redirectUri),
                'response_type' => $authorizationRequest->responseType,
                'scope_string' => implode(' ', $allowedScopes),
                'state_hash' => hash('sha256', $authorizationRequest->state),
                'nonce_hash' => $authorizationRequest->nonce === null ? null : hash('sha256', $authorizationRequest->nonce),
                'nonce_encrypted' => $authorizationRequest->nonce === null ? null : encrypt($authorizationRequest->nonce),
                'code_challenge' => $authorizationRequest->codeChallenge,
                'code_challenge_method' => $authorizationRequest->codeChallengeMethod,
                'status' => AuthorizationTransactionStatus::Pending,
                'expires_at' => now()->addMinutes(10),
            ]);

            $request->session()->put('oauth.authorization.'.$transaction->getKey(), [
                'redirect_uri' => $authorizationRequest->redirectUri,
                'state' => $authorizationRequest->state,
            ]);

            return response('Authorization consent required', Response::HTTP_OK);
        } catch (InvalidArgumentException|ModelNotFoundException) {
            return response('Invalid authorization request.', Response::HTTP_BAD_REQUEST);
        }
    }
}
