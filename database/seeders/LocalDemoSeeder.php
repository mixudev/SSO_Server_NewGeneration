<?php

namespace Database\Seeders;

use App\Models\Identity\Application;
use App\Models\Identity\ApplicationRedirectUri;
use App\Models\Identity\Claim;
use App\Models\Identity\Organization;
use App\Models\Identity\Scope;
use Illuminate\Database\Seeder;

class LocalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $organization = Organization::query()->updateOrCreate(
            ['slug' => 'local-demo-organization'],
            [
                'name' => 'Local Demo Organization',
                'status' => 'active',
                'settings_json' => [],
            ],
        );

        $scopes = collect([
            ['name' => 'profile:read', 'description' => 'Read basic profile information.', 'category' => 'identity', 'risk_level' => 'low'],
            ['name' => 'email:read', 'description' => 'Read the signed-in email address.', 'category' => 'identity', 'risk_level' => 'low'],
        ])->mapWithKeys(function (array $attributes): array {
            $scope = Scope::query()->updateOrCreate(
                ['name' => $attributes['name']],
                $attributes + ['is_system' => false, 'is_default' => false, 'status' => 'active'],
            );

            return [$scope->name => $scope];
        });

        collect([
            ['key' => 'user.email', 'description' => 'Signed-in user email.', 'source' => 'email', 'value_type' => 'string', 'sensitivity' => 'personal'],
            ['key' => 'user.name', 'description' => 'Signed-in user display name.', 'source' => 'name', 'value_type' => 'string', 'sensitivity' => 'personal'],
        ])->each(function (array $attributes): void {
            Claim::query()->updateOrCreate(
                ['key' => $attributes['key']],
                $attributes + ['status' => 'active'],
            );
        });

        $application = Application::query()->updateOrCreate(
            ['organization_id' => $organization->getKey(), 'slug' => 'local-demo-client'],
            [
                'name' => 'Local Demo Client',
                'protocol_mode' => 'oidc',
                'client_type' => 'public_spa',
                'status' => 'draft',
                'description' => 'Local-only demo application. No credential is issued.',
                'consent_policy' => 'explicit',
                'session_policy_json' => [],
                'claim_policy_version' => 1,
            ],
        );

        ApplicationRedirectUri::query()->updateOrCreate(
            [
                'application_id' => $application->getKey(),
                'kind' => 'login',
                'uri_hash' => hash('sha256', 'http://localhost:3000/auth/callback'),
            ],
            [
                'uri' => 'http://localhost:3000/auth/callback',
            ],
        );

        $application->scopes()->sync($scopes->pluck('id')->mapWithKeys(fn (string $scopeId): array => [
            $scopeId => ['allowed' => true, 'consent_required' => true],
        ])->all());
    }
}
