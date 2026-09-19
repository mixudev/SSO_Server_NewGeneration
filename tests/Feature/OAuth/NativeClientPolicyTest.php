<?php

namespace Tests\Feature\OAuth;

use App\Domain\Applications\Services\ApplicationCredentialService;
use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NativeClientPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_native_client_cannot_receive_credentials_before_native_redirect_policy_exists(): void
    {
        $organization = Organization::factory()->create(['status' => 'active']);
        $application = Application::factory()->create([
            'organization_id' => $organization->getKey(),
            'status' => 'active',
            'client_type' => 'native',
        ]);
        $admin = User::factory()->create();

        $this->expectExceptionMessage('Application client type is unsupported.');

        app(ApplicationCredentialService::class)
            ->issue($application, (string) $admin->getAuthIdentifier());

        $this->assertDatabaseCount('oauth_clients', 0);
    }
}
