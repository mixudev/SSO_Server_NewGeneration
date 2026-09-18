<?php

namespace App\Infrastructure\Identity;

use Illuminate\Contracts\Auth\Authenticatable;
use Vendor\LaravelAuthentication\Contracts\AuditLoggerInterface;
use Vendor\LaravelAuthentication\Services\Passkey\PasskeyService;
use Vendor\LaravelAuthentication\Services\Session\SessionManagerService;
use Vendor\LaravelAuthentication\Services\TwoFactor\TwoFactorService;

final class ProfileSecuritySummary
{
    /**
     * @return array{two_factor_enabled: bool, passkeys: array<int, array{id:int|string,name:string,created_at:string|null,last_used_at:string|null}>, recent_logins: array<int, array<string, mixed>>}
     */
    public function for(Authenticatable $user, ?string $currentSessionId = null): array
    {
        $passkeys = app(PasskeyService::class)->getUserPasskeys($user)
            ->map(static fn ($passkey): array => [
                'id' => $passkey->getKey(),
                'name' => (string) $passkey->name,
                'created_at' => $passkey->created_at?->toIso8601String(),
                'last_used_at' => $passkey->last_used_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        $recentLogins = app(AuditLoggerInterface::class)->getRecentLogins($user, 5);

        return [
            'two_factor_enabled' => app(TwoFactorService::class)->isEnabledFor($user),
            'passkeys' => $passkeys,
            'recent_logins' => array_map(static fn (array $login): array => [
                'ip_address' => $login['ip_address'] ?? null,
                'user_agent' => $login['user_agent'] ?? null,
                'login_method' => $login['login_method'] ?? 'unknown',
                'login_at' => $login['login_at'] ?? null,
            ], $recentLogins),
            'sessions' => app(SessionManagerService::class)->getActiveSessions($user, $currentSessionId),
        ];
    }
}
