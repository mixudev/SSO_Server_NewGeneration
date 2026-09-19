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
            ->assertJsonPath('checks.signing_key', 'ok')
            ->assertJsonPath('key_status.active', true)
            ->assertJsonPath('key_status.verification_key_count', 1)
            ->assertJsonMissing(['private_key' => true]);
    }
}
