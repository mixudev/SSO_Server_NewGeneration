<?php

namespace App\Domain\Identity\Contracts;

use App\Models\Identity\SecurityEvent;
use DateTimeInterface;

interface AuditLoggerInterface
{
    /**
     * @param  array<string, mixed>  $metadata
     */
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
    ): SecurityEvent;
}
