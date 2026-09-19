<?php

namespace Tests\Feature\Portal;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationCredential;
use App\Models\Identity\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ApplicationPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_only_active_assigned_applications(): void
    {
        $user = User::factory()->create(['active' => true, 'status' => 'active']);
        $allowed = $this->application('allowed');
        $hidden = $this->application('hidden');
        $allowed->userAccess()->create(['user_id' => $user->getKey(), 'status' => 'active']);
        $hidden->userAccess()->create(['user_id' => $user->getKey(), 'status' => 'revoked']);

        $this->actingAs($user)
            ->get(route('sso.portal'))
            ->assertOk()
            ->assertSee($allowed->name)
            ->assertSee($allowed->homepage_url)
            ->assertDontSee('>'.$hidden->name.'<');
    }

    public function test_direct_portal_access_requires_authentication(): void
    {
        $this->get(route('sso.portal'))->assertRedirect();
    }

    public function test_inactive_application_is_not_listed_even_with_active_assignment(): void
    {
        $user = User::factory()->create(['active' => true, 'status' => 'active']);
        $application = $this->application('inactive', 'suspended');
        $application->userAccess()->create(['user_id' => $user->getKey(), 'status' => 'active']);

        $this->actingAs($user)
            ->get(route('sso.portal'))
            ->assertOk()
            ->assertDontSee($application->name);
    }

    /** @param array{0?: string, 1?: string} $values */
    private function application(string $name, string $status = 'active'): Application
    {
        $application = Application::factory()->create([
            'name' => $name,
            'status' => $status,
            'homepage_url' => 'https://'.$name.'.example.test/login',
            'organization_id' => Organization::factory()->create(['status' => 'active'])->getKey(),
        ]);
        ApplicationCredential::query()->create([
            'application_id' => $application->getKey(),
            'passport_client_id' => 'passport-'.$name,
            'status' => 'active',
            'generation' => 1,
        ]);

        return $application;
    }
}
