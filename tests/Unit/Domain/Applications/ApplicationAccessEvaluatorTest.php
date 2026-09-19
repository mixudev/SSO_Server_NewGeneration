<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Applications;

use App\Domain\Applications\Services\ApplicationAccessEvaluator;
use App\Models\Identity\Application;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ApplicationAccessEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_is_denied_without_an_explicit_assignment(): void
    {
        $user = User::factory()->create(['active' => true, 'status' => 'active']);
        $application = Application::factory()->create(['status' => 'active']);

        self::assertFalse(app(ApplicationAccessEvaluator::class)->canLaunch($user, $application));
    }

    public function test_access_is_granted_for_an_active_explicit_assignment(): void
    {
        $user = User::factory()->create(['active' => true, 'status' => 'active']);
        $application = Application::factory()->create([
            'status' => 'active',
            'organization_id' => Organization::factory()->create(['status' => 'active'])->getKey(),
        ]);

        $application->userAccess()->create([
            'user_id' => $user->getKey(),
            'status' => 'active',
        ]);

        self::assertTrue(app(ApplicationAccessEvaluator::class)->canLaunch($user, $application));
    }

    #[DataProvider('deniedLifecycleStates')]
    public function test_access_is_denied_for_inactive_lifecycle_records(string $userStatus, bool $userActive, string $organizationStatus, string $applicationStatus): void
    {
        $user = User::factory()->create(['active' => $userActive, 'status' => $userStatus]);
        $organization = Organization::factory()->create(['status' => $organizationStatus]);
        $application = Application::factory()->for($organization)->create(['status' => $applicationStatus]);
        $application->userAccess()->create([
            'user_id' => $user->getKey(),
            'status' => 'active',
        ]);

        self::assertFalse(app(ApplicationAccessEvaluator::class)->canLaunch($user, $application));
    }

    /** @return array<string, array{string, bool, string, string}> */
    public static function deniedLifecycleStates(): array
    {
        return [
            'inactive user' => ['active', false, 'active', 'active'],
            'inactive organization' => ['active', true, 'inactive', 'active'],
            'draft application' => ['active', true, 'active', 'draft'],
            'revoked application' => ['active', true, 'active', 'revoked'],
        ];
    }

    public function test_access_is_denied_for_a_revoked_assignment(): void
    {
        $user = User::factory()->create(['active' => true, 'status' => 'active']);
        $application = Application::factory()->create(['status' => 'active']);
        $application->userAccess()->create([
            'user_id' => $user->getKey(),
            'status' => 'revoked',
        ]);

        self::assertFalse(app(ApplicationAccessEvaluator::class)->canLaunch($user, $application));
    }
}
