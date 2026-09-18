<?php

namespace App\Infrastructure\Identity;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Models\Identity\SecurityEvent;
use DateTimeInterface;
use InvalidArgumentException;

final class SecurityAuditLogger implements AuditLoggerInterface
{
    private const SENSITIVE_KEYS = [
        'password', 'secret', 'token', 'private_key', 'client_secret', 'authorization', 'code',
    ];

    public function record(
        string $event,
        ?string $requestId = null,
        ?int $organizationId = null,
        ?int $applicationId = null,
        ?string $subject = null,
        ?string $actor = null,
        string $risk = 'medium',
        array $metadata = [],
        ?DateTimeInterface $occurredAt = null,
    ): SecurityEvent {
        if (trim($event) === '') {
            throw new InvalidArgumentException('Security event name is required.');
        }

        if (! in_array($risk, ['low', 'medium', 'high', 'critical'], true)) {
            throw new InvalidArgumentException('Invalid security event risk.');
        }

        return SecurityEvent::query()->create([
            'event' => $event,
            'request_id' => $requestId,
            'organization_id' => $organizationId,
            'application_id' => $applicationId,
            'subject' => $subject,
            'actor' => $actor,
            'risk' => $risk,
            'metadata' => $this->redact($metadata),
            'occurred_at' => $occurredAt ?? now(),
        ]);
    }

    /** @param array<string, mixed> $values */
    private function redact(array $values): array
    {
        $redacted = [];

        foreach ($values as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            $redacted[$key] = in_array($normalizedKey, self::SENSITIVE_KEYS, true)
                ? '[REDACTED]'
                : (is_array($value) ? $this->redact($value) : $value);
        }

        return $redacted;
    }
}
