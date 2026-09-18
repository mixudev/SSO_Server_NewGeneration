<?php

namespace Tests\Feature\Admin;

use App\Models\Identity\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApplicationsIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('security-defense.middleware.payload_scanner.enabled', false);
    }

    public function test_guest_and_unauthorized_users_cannot_access_applications(): void
    {
        $this->get('/admin/applications')->assertRedirect();

        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/applications')->assertForbidden();
    }

    public function test_authorized_user_can_search_filter_and_paginate_applications(): void
    {
        $user = $this->authorizedUser();
        Application::factory()->create(['name' => 'Billing Portal', 'status' => 'active']);
        Application::factory()->create(['name' => 'Internal Draft', 'status' => 'draft']);
        Application::factory()->count(11)->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/admin/applications?search=Billing&status=active');

        $response->assertOk()
            ->assertViewIs('pages.admin.applications.index')
            ->assertSee('Billing Portal')
            ->assertDontSee('Internal Draft')
            ->assertSee('Applications');
    }

    public function test_authorized_user_sees_empty_state_when_no_application_matches(): void
    {
        $response = $this->actingAs($this->authorizedUser())
            ->get('/admin/applications?search=does-not-exist');

        $response->assertOk()
            ->assertSee('No applications found');
    }

    private function authorizedUser(): User
    {
        Permission::findOrCreate('admin.dashboard.view', 'web');
        Permission::findOrCreate('applications.view', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.dashboard.view', 'applications.view']);

        return $user;
    }
}
