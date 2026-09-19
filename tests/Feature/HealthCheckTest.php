<?php

namespace Tests\Feature;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_reports_database_and_signing_key_state(): void
    {
        app(KeyManagerInterface::class)->generate();

        $this->getJson('/health/ready')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database', 'ok')
            ->assertJsonPath('checks.signing_key', 'ok');
    }
}
