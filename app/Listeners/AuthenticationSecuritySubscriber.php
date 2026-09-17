<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Events\Dispatcher;
use Mixudev\SecurityDefense\Support\Facades\SecurityDefense;
use Vendor\LaravelAuthentication\Events\AccountLocked;
use Vendor\LaravelAuthentication\Events\EmailVerified;
use Vendor\LaravelAuthentication\Events\LoginFailed;
use Vendor\LaravelAuthentication\Events\LoginSucceeded;
use Vendor\LaravelAuthentication\Events\LogoutPerformed;
use Vendor\LaravelAuthentication\Events\NewDeviceLoginDetected;
use Vendor\LaravelAuthentication\Events\OtpVerified;
use Vendor\LaravelAuthentication\Events\PasswordChanged;
use Vendor\LaravelAuthentication\Events\PasswordResetCompleted;
use Vendor\LaravelAuthentication\Events\PasswordResetRequested;
use Vendor\LaravelAuthentication\Events\SessionRevoked;
use Vendor\LaravelAuthentication\Events\UserRegistered;

class AuthenticationSecuritySubscriber
{
    public function handleLoginFailed(LoginFailed $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => $event->identifier,
            'eventType' => 'LoginFailed',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'reason' => $event->reason,
                'user_id' => $event->user?->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleLoginSucceeded(LoginSucceeded $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user->email ?? $event->user->username ?? $event->user->getAuthIdentifier()),
            'eventType' => 'LoginSucceeded',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'strategy' => $event->strategy,
                'user_id' => $event->user->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleAccountLocked(AccountLocked $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user->email ?? $event->user->username ?? $event->user->getAuthIdentifier()),
            'eventType' => 'AccountLocked',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'lockout_duration_minutes' => $event->lockoutDurationMinutes,
                'user_id' => $event->user->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleNewDeviceLoginDetected(NewDeviceLoginDetected $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user->email ?? $event->user->username ?? $event->user->getAuthIdentifier()),
            'eventType' => 'NewDeviceLoginDetected',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'device_id' => $event->device->id,
                'user_id' => $event->user->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleOtpVerified(OtpVerified $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => $event->identifier,
            'eventType' => 'OTP_VERIFIED',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'user_id' => $event->user->getAuthIdentifier(),
            ],
        ]);
    }

    public function handlePasswordChanged(PasswordChanged $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user->email ?? $event->user->username ?? $event->user->getAuthIdentifier()),
            'eventType' => 'PasswordChanged',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'user_id' => $event->user->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleSessionRevoked(SessionRevoked $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user?->email ?? $event->user?->username ?? ($event->user ? $event->user->getAuthIdentifier() : 'anonymous')),
            'eventType' => 'SessionRevoked',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'session_id' => $event->sessionId,
                'user_id' => $event->user?->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleLogoutPerformed(LogoutPerformed $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user?->email ?? $event->user?->username ?? ($event->user ? $event->user->getAuthIdentifier() : 'anonymous')),
            'eventType' => 'LogoutPerformed',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'user_id' => $event->user?->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleUserRegistered(UserRegistered $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user->email ?? $event->user->username ?? $event->user->getAuthIdentifier()),
            'eventType' => 'UserRegistered',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'user_id' => $event->user->getAuthIdentifier(),
            ],
        ]);
    }

    public function handlePasswordResetRequested(PasswordResetRequested $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user?->email ?? $event->user?->username ?? ($event->user ? $event->user->getAuthIdentifier() : 'anonymous')),
            'eventType' => 'PasswordResetRequested',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'user_id' => $event->user?->getAuthIdentifier(),
            ],
        ]);
    }

    public function handlePasswordResetCompleted(PasswordResetCompleted $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user->email ?? $event->user->username ?? $event->user->getAuthIdentifier()),
            'eventType' => 'PasswordResetCompleted',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'user_id' => $event->user->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleEmailVerified(EmailVerified $event): void
    {
        SecurityDefense::record([
            'ip' => $event->context->ipAddress,
            'identifier' => (string) ($event->user->email ?? $event->user->username ?? $event->user->getAuthIdentifier()),
            'eventType' => 'EmailVerified',
            'userAgent' => $event->context->userAgent,
            'metadata' => [
                'user_id' => $event->user->getAuthIdentifier(),
            ],
        ]);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            LoginFailed::class => 'handleLoginFailed',
            LoginSucceeded::class => 'handleLoginSucceeded',
            AccountLocked::class => 'handleAccountLocked',
            NewDeviceLoginDetected::class => 'handleNewDeviceLoginDetected',
            OtpVerified::class => 'handleOtpVerified',
            PasswordChanged::class => 'handlePasswordChanged',
            SessionRevoked::class => 'handleSessionRevoked',
            LogoutPerformed::class => 'handleLogoutPerformed',
            UserRegistered::class => 'handleUserRegistered',
            PasswordResetRequested::class => 'handlePasswordResetRequested',
            PasswordResetCompleted::class => 'handlePasswordResetCompleted',
            EmailVerified::class => 'handleEmailVerified',
        ];
    }
}
