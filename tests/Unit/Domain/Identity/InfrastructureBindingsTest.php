<?php

namespace Tests\Unit\Domain\Identity;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Infrastructure\Identity\LocalRsaKeyManager;
use App\Infrastructure\Identity\SecurityAuditLogger;
use Tests\TestCase;

class InfrastructureBindingsTest extends TestCase
{
    public function test_identity_ports_resolve_to_singleton_adapters(): void
    {
        $keyManager = app(KeyManagerInterface::class);
        $auditLogger = app(AuditLoggerInterface::class);

        $this->assertInstanceOf(LocalRsaKeyManager::class, $keyManager);
        $this->assertInstanceOf(SecurityAuditLogger::class, $auditLogger);
        $this->assertSame($keyManager, app(KeyManagerInterface::class));
        $this->assertSame($auditLogger, app(AuditLoggerInterface::class));
    }
}
