<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
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
    public function __construct(private AuditLoggerInterface $auditLogger) {}

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
        $this->auditLogger->record('LOGIN_FAILURE', subject: $event->user?->getAuthIdentifier() !== null ? (string) $event->user->getAuthIdentifier() : null, risk: 'high', metadata: ['reason' => substr((string) $event->reason, 0, 120)]);
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
        $this->auditLogger->record('LOGIN_SUCCESS', subject: (string) $event->user->getAuthIdentifier(), risk: 'low', metadata: ['strategy' => substr((string) $event->strategy, 0, 64)]);
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
        $this->auditLogger->record('ACCOUNT_LOCKED', subject: (string) $event->user->getAuthIdentifier(), risk: 'high', metadata: ['duration_minutes' => (int) $event->lockoutDurationMinutes]);
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
        $this->auditLogger->record('NEW_DEVICE_LOGIN', subject: (string) $event->user->getAuthIdentifier(), risk: 'medium', metadata: ['device_id' => (string) $event->device->id]);
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
        $this->auditLogger->record('OTP_VERIFIED', subject: (string) $event->user->getAuthIdentifier(), risk: 'low');
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
        $this->auditLogger->record('PASSWORD_CHANGED', subject: (string) $event->user->getAuthIdentifier(), risk: 'high');
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
        $this->auditLogger->record('SESSION_REVOKED', subject: $event->user?->getAuthIdentifier() !== null ? (string) $event->user->getAuthIdentifier() : null, risk: 'high');
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
        $this->auditLogger->record('LOGOUT', subject: $event->user?->getAuthIdentifier() !== null ? (string) $event->user->getAuthIdentifier() : null, risk: 'low');
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
        $this->auditLogger->record('USER_REGISTERED', subject: (string) $event->user->getAuthIdentifier(), risk: 'medium');
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
        $this->auditLogger->record('PASSWORD_RESET_REQUESTED', subject: $event->user?->getAuthIdentifier() !== null ? (string) $event->user->getAuthIdentifier() : null, risk: 'medium');
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
        $this->auditLogger->record('PASSWORD_RESET_COMPLETED', subject: (string) $event->user->getAuthIdentifier(), risk: 'high');
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
        $this->auditLogger->record('EMAIL_VERIFIED', subject: (string) $event->user->getAuthIdentifier(), risk: 'low');
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
