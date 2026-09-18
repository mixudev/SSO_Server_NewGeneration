<?php

namespace Tests\Feature\Identity;

use App\Infrastructure\Identity\SecurityAuditLogger;
use App\Models\Identity\SecurityEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SecurityAuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_required_security_event_metadata_and_redacts_secrets(): void
    {
        $event = (new SecurityAuditLogger)->record(
            event: 'SIGNING_KEY_ROTATED',
            requestId: 'req-123',
            organizationId: 7,
            applicationId: 9,
            subject: 'user-1',
            actor: 'admin-1',
            risk: 'high',
            metadata: [
                'kid' => 'public-kid',
                'token' => 'do-not-store',
                'nested' => ['client_secret' => 'also-do-not-store'],
            ],
        );

        $this->assertInstanceOf(SecurityEvent::class, $event);
        $this->assertSame('SIGNING_KEY_ROTATED', $event->event);
        $this->assertSame('[REDACTED]', $event->metadata['token']);
        $this->assertSame('[REDACTED]', $event->metadata['nested']['client_secret']);
        $this->assertSame('public-kid', $event->metadata['kid']);
    }

    public function test_rejects_empty_event(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SecurityAuditLogger)->record(event: '', risk: 'high');
    }

    public function test_rejects_unknown_risk(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SecurityAuditLogger)->record(event: 'UNKNOWN_EVENT', risk: 'unknown');
    }
}
