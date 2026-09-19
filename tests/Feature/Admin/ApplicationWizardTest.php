<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\Identity\Claim;
use App\Models\Identity\Organization;
use App\Models\Identity\Scope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApplicationWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('security-defense.middleware.payload_scanner.enabled', false);
    }

    public function test_wizard_requires_create_permission(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('admin.dashboard.view', 'web');
        $user->givePermissionTo('admin.dashboard.view');

        $this->actingAs($user)->get('/admin/applications/create')->assertForbidden();
    }

    public function test_wizard_rejects_skipped_steps(): void
    {
        $user = $this->authorizedUser();

        $this->actingAs($user)
            ->get(route('admin.applications.wizard.protocol'))
            ->assertRedirect(route('admin.applications.wizard.basic'));
    }

    public function test_basic_step_rejects_inactive_organization_before_storing_wizard_state(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create(['status' => 'suspended']);

        $this->actingAs($user)
            ->post(route('admin.applications.wizard.basic.store'), [
                'organization_id' => $organization->getKey(),
                'name' => 'Inactive Organization Client',
                'slug' => 'inactive-organization-client',
            ])
            ->assertSessionHasErrors('organization_id');

        $this->assertFalse(session()->has('application_wizard.basic'));
    }

    public function test_basic_step_lists_only_active_organizations(): void
    {
        $user = $this->authorizedUser();
        $active = Organization::factory()->create(['name' => 'Active Organization']);
        $inactive = Organization::factory()->create(['name' => 'Suspended Organization', 'status' => 'suspended']);

        $this->actingAs($user)
            ->get(route('admin.applications.wizard.basic'))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);
    }

    public function test_wizard_rejects_scope_and_claim_steps_without_previous_steps(): void
    {
        $user = $this->authorizedUser();
        $session = $this->actingAs($user);

        $session->get(route('admin.applications.wizard.scopes'))
            ->assertRedirect(route('admin.applications.wizard.basic'));
        $session->get(route('admin.applications.wizard.claims'))
            ->assertRedirect(route('admin.applications.wizard.basic'));
    }

    public function test_authorized_user_can_complete_wizard_with_scopes_and_claim_policy(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $scope = Scope::factory()->create(['name' => 'account:read', 'status' => 'active']);
        $claim = Claim::factory()->create(['key' => 'user.email', 'status' => 'active']);
        $session = $this->actingAs($user);

        $this->completeBasicProtocolAndRedirect($session, $organization);

        $session->post(route('admin.applications.wizard.scopes.store'), [
            'scope_ids' => [$scope->id],
        ])->assertRedirect(route('admin.applications.wizard.claims'));
        $session->post(route('admin.applications.wizard.claims.store'), [
            'claim_keys' => [$claim->key],
        ])->assertRedirect(route('admin.applications.wizard.security'));
        $session->post(route('admin.applications.wizard.security.store'), [
            'consent_policy' => 'explicit',
            'session_max_age' => 3600,
            'session_idle_timeout' => 900,
        ])->assertRedirect(route('admin.applications.wizard.review'));

        $session->get(route('admin.applications.wizard.review'))
            ->assertOk()
            ->assertSee('account:read')
            ->assertSee('user.email')
            ->assertSee('explicit');
        $session->post(route('admin.applications.wizard.complete'))
            ->assertRedirect(route('admin.applications.index'));

        $application = Application::query()->where('slug', 'portal')->firstOrFail();
        $this->assertSame(1, $application->claim_policy_version);
        $this->assertSame(['user.email'], $application->session_policy_json['claims']);
        $this->assertDatabaseHas('application_claim_policies', [
            'application_id' => $application->id,
            'version' => 1,
            'status' => 'active',
        ]);
        $this->assertTrue($application->scopes()->whereKey($scope->id)->exists());
    }

    public function test_wizard_rejects_unknown_inactive_and_duplicate_scope_ids(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $inactiveScope = Scope::factory()->create(['status' => 'inactive']);
        $session = $this->actingAs($user);
        $this->completeBasicProtocolAndRedirect($session, $organization);

        $session->post(route('admin.applications.wizard.scopes.store'), [
            'scope_ids' => [$inactiveScope->id, $inactiveScope->id, '01invalidscopeid00000000000000'],
        ])->assertSessionHasErrors('scope_ids');

        $session->get(route('admin.applications.wizard.claims'))->assertOk();
        $this->assertSame([], session('application_wizard.scope_ids'));
    }

    public function test_wizard_ignores_browser_supplied_claim_policy_version(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create(['status' => 'active']);
        $claim = Claim::factory()->create(['key' => 'user.email', 'status' => 'active']);
        $session = $this->actingAs($user);

        $this->completeBasicProtocolAndRedirect($session, $organization);
        $session->post(route('admin.applications.wizard.scopes.store'), ['scope_ids' => []]);
        $session->post(route('admin.applications.wizard.claims.store'), [
            'claim_keys' => [$claim->key],
            'claim_policy_version' => 999,
        ])->assertRedirect(route('admin.applications.wizard.security'));
        $session->post(route('admin.applications.wizard.security.store'), [
            'consent_policy' => 'explicit',
            'session_max_age' => 3600,
            'session_idle_timeout' => 900,
        ]);
        $session->post(route('admin.applications.wizard.complete'))
            ->assertRedirect(route('admin.applications.index'));

        $application = Application::query()->where('organization_id', $organization->id)->firstOrFail();
        $this->assertSame(1, $application->claim_policy_version);
        $this->assertDatabaseHas('application_claim_policies', [
            'application_id' => $application->id,
            'version' => 1,
            'status' => 'active',
        ]);
    }

    public function test_wizard_rejects_unknown_inactive_and_duplicate_claim_keys(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $inactiveClaim = Claim::factory()->create(['key' => 'user.phone', 'status' => 'inactive']);
        $session = $this->actingAs($user);
        $this->completeBasicProtocolAndRedirect($session, $organization);
        $session->post(route('admin.applications.wizard.scopes.store'), ['scope_ids' => []]);

        $session->post(route('admin.applications.wizard.claims.store'), [
            'claim_keys' => [$inactiveClaim->key, $inactiveClaim->key, 'user.unknown'],
            'claim_policy_version' => 1,
        ])->assertSessionHasErrors('claim_keys');

        $this->assertDatabaseMissing('applications', ['slug' => 'portal']);
    }

    public function test_security_policy_rejects_invalid_values_and_public_spa_long_idle_timeout(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $session = $this->actingAs($user);
        $this->completeBasicProtocolAndRedirect($session, $organization, 'public_spa');

        $session->post(route('admin.applications.wizard.security.store'), [
            'consent_policy' => 'invalid',
            'session_max_age' => 100,
            'session_idle_timeout' => 901,
        ])->assertSessionHasErrors(['consent_policy', 'session_max_age', 'session_idle_timeout']);

        $session->post(route('admin.applications.wizard.security.store'), [
            'consent_policy' => 'explicit',
            'session_max_age' => 3600,
            'session_idle_timeout' => 900,
        ])->assertSessionHasErrors('session_idle_timeout');
    }

    public function test_browser_string_security_values_can_complete_wizard(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $session = $this->actingAs($user);
        $this->completeBasicProtocolAndRedirect($session, $organization);

        $session->post(route('admin.applications.wizard.security.store'), [
            'consent_policy' => 'explicit',
            'session_max_age' => '3600',
            'session_idle_timeout' => '900',
        ])->assertRedirect(route('admin.applications.wizard.review'));

        $session->post(route('admin.applications.wizard.complete'))
            ->assertRedirect(route('admin.applications.index'));

        $this->assertDatabaseHas('applications', ['slug' => 'portal']);
    }

    public function test_completion_rejects_scope_deactivated_after_selection(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $scope = Scope::factory()->create(['status' => 'active']);
        $session = $this->actingAs($user);
        $this->completeBasicProtocolAndRedirect($session, $organization);
        $session->post(route('admin.applications.wizard.scopes.store'), ['scope_ids' => [$scope->id]]);
        $session->post(route('admin.applications.wizard.claims.store'), ['claim_keys' => [], 'claim_policy_version' => 1]);
        $session->post(route('admin.applications.wizard.security.store'), ['consent_policy' => 'explicit', 'session_max_age' => 3600, 'session_idle_timeout' => 900]);
        $scope->update(['status' => 'inactive']);

        $session->post(route('admin.applications.wizard.complete'))
            ->assertSessionHasErrors('registry');
        $this->assertDatabaseMissing('applications', ['slug' => 'portal']);
    }

    public function test_invalid_redirect_uri_does_not_reach_review(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $session = $this->actingAs($user);

        $session->post(route('admin.applications.wizard.basic.store'), [
            'organization_id' => $organization->id,
            'name' => 'Unsafe',
            'slug' => 'unsafe',
        ]);
        $session->post(route('admin.applications.wizard.protocol.store'), [
            'protocol_mode' => 'oidc',
            'client_type' => 'public_spa',
        ]);

        $session->post(route('admin.applications.wizard.redirect.store'), [
            'redirect_uris' => ['https://client.example/callback#fragment'],
        ])->assertSessionHasErrors('redirect_uris.0');

        $this->assertDatabaseMissing('applications', ['slug' => 'unsafe']);
    }

    public function test_completion_rejects_tampered_security_policy(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $session = $this->actingAs($user);
        $this->completeBasicProtocolAndRedirect($session, $organization, 'public_spa');

        session()->put('application_wizard.security.session_idle_timeout', 900);

        $session->post(route('admin.applications.wizard.complete'))
            ->assertSessionHasErrors('security');
        $this->assertDatabaseMissing('applications', ['slug' => 'portal']);
    }

    public function test_completion_revalidates_tampered_redirect_uris(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $session = $this->actingAs($user);
        $this->completeBasicProtocolAndRedirect($session, $organization);

        session()->put('application_wizard.redirect_uris', ['https://client.example/callback#fragment']);

        $session->post(route('admin.applications.wizard.complete'))
            ->assertSessionHasErrors('redirect_uris.0');
        $this->assertDatabaseMissing('applications', ['slug' => 'portal']);
    }

    public function test_completion_rejects_inactive_organization(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create(['status' => 'inactive']);
        $session = $this->actingAs($user);
        $this->completeBasicProtocolAndRedirect($session, $organization);

        $session->post(route('admin.applications.wizard.complete'))
            ->assertSessionHasErrors('wizard');
        $this->assertDatabaseMissing('applications', ['slug' => 'portal']);
    }

    public function test_review_rejects_malformed_scope_and_claim_segments(): void
    {
        $user = $this->authorizedUser();
        $organization = Organization::factory()->create();
        $session = $this->actingAs($user);
        $this->completeBasicProtocolAndRedirect($session, $organization);

        session()->put('application_wizard.scope_ids', [['malformed']]);
        session()->put('application_wizard.claims.keys', [['malformed']]);

        $session->get(route('admin.applications.wizard.review'))
            ->assertRedirect(route('admin.applications.wizard.basic'))
            ->assertSessionHasErrors('scope_ids');
    }

    private function completeBasicProtocolAndRedirect($session, Organization $organization, string $clientType = 'confidential_web'): void
    {
        $session->post(route('admin.applications.wizard.basic.store'), [
            'organization_id' => $organization->id,
            'name' => 'Portal',
            'slug' => 'portal',
            'description' => 'Portal application',
        ]);
        $session->post(route('admin.applications.wizard.protocol.store'), [
            'protocol_mode' => 'oidc',
            'client_type' => $clientType,
        ]);
        $session->post(route('admin.applications.wizard.redirect.store'), [
            'redirect_uris' => ['HTTPS://CLIENT.EXAMPLE/callback'],
        ]);
        $session->post(route('admin.applications.wizard.scopes.store'), ['scope_ids' => []]);
        $session->post(route('admin.applications.wizard.claims.store'), [
            'claim_keys' => [],
            'claim_policy_version' => 1,
        ]);
        $session->post(route('admin.applications.wizard.security.store'), [
            'consent_policy' => 'explicit',
            'session_max_age' => 3600,
            'session_idle_timeout' => 900,
        ]);
    }

    private function authorizedUser(): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('applications.create', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', 'applications.create']);

        return $user;
    }
}
