<?php

namespace App\Http\Controllers\Oidc;

use App\Domain\Identity\Services\ClaimPolicyEngine;
use App\Http\Controllers\Controller;
use App\Models\Identity\ApplicationCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserInfoController extends Controller
{
    public function __construct(private ClaimPolicyEngine $claimPolicyEngine) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $client = auth('api')->client();
        $credential = $client === null
            ? null
            : ApplicationCredential::query()->with('application.activeClaimPolicy')->where('passport_client_id', $client->getKey())->first();
        $policy = $credential?->application?->activeClaimPolicy?->rules_json;

        if (is_array($policy) && isset($policy['claims'])) {
            $claims = $this->claimPolicyEngine->resolve(
                $policy,
                $this->requestedScopes($user),
                [
                    'user.name' => $user->name,
                    'user.email' => $user->email,
                    'user.email_verified' => $user->email_verified_at !== null,
                ],
            );

            return response()->json(['sub' => (string) $user->uuid, ...$claims]);
        }

        return response()->json(array_filter([
            'sub' => (string) $user->uuid,
            'name' => $user->tokenCan('profile') ? $user->name : null,
            'email' => $user->tokenCan('email') ? $user->email : null,
            'email_verified' => $user->tokenCan('email') && $user->email_verified_at !== null,
        ], static fn (mixed $value): bool => $value !== null));
    }

    /** @return list<string> */
    private function requestedScopes(object $user): array
    {
        return array_values(array_filter(['openid', 'profile', 'email'], fn (string $scope): bool => $user->tokenCan($scope)));
    }
}
