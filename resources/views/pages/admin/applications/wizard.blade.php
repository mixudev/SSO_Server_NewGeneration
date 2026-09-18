<x-dashboard.layout
    title="Create application"
    breadcrumb="Applications / Create"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="max-w-3xl space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--dash-primary)]">Application wizard</p>
            <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">Create application</h1>
            <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Complete each server-validated step before reviewing the draft.</p>
        </div>

        @if($step === 'basic')
            <form method="POST" action="{{ route('admin.applications.wizard.basic.store') }}" class="space-y-4 border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                @csrf
                <label class="block text-sm font-medium">Organization ID<input name="organization_id" value="{{ old('organization_id', data_get($wizard, 'basic.organization_id')) }}" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2" required></label>
                <label class="block text-sm font-medium">Name<input name="name" value="{{ old('name', data_get($wizard, 'basic.name')) }}" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2" required></label>
                <label class="block text-sm font-medium">Slug<input name="slug" value="{{ old('slug', data_get($wizard, 'basic.slug')) }}" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2" required></label>
                <label class="block text-sm font-medium">Description<textarea name="description" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2">{{ old('description', data_get($wizard, 'basic.description')) }}</textarea></label>
                <x-form.button type="submit" size="sm">Continue</x-form.button>
            </form>
        @elseif($step === 'protocol')
            <form method="POST" action="{{ route('admin.applications.wizard.protocol.store') }}" class="space-y-4 border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                @csrf
                <label class="block text-sm font-medium">Protocol<select name="protocol_mode" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2"><option value="oauth2">OAuth 2.0</option><option value="oidc">OpenID Connect</option></select></label>
                <label class="block text-sm font-medium">Client type<select name="client_type" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2"><option value="confidential_web">Confidential web</option><option value="public_spa">Public SPA</option><option value="native">Native</option></select></label>
                <x-form.button type="submit" size="sm">Continue</x-form.button>
            </form>
        @elseif($step === 'redirect')
            <form method="POST" action="{{ route('admin.applications.wizard.redirect.store') }}" class="space-y-4 border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                @csrf
                <label class="block text-sm font-medium">Redirect URI<input name="redirect_uris[]" value="{{ old('redirect_uris.0') }}" placeholder="https://client.example/callback" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2" required></label>
                <x-form.button type="submit" size="sm">Review</x-form.button>
            </form>
        @elseif($step === 'scopes')
            <form method="POST" action="{{ route('admin.applications.wizard.scopes.store') }}" class="space-y-4 border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                @csrf
                <fieldset class="space-y-3"><legend class="text-sm font-semibold">Allowed scopes</legend>
                    @forelse($scopes as $scope)
                        <label class="flex items-start gap-3 text-sm"><input type="checkbox" name="scope_ids[]" value="{{ $scope->id }}" @checked(in_array($scope->id, data_get($wizard, 'scope_ids', []), true))><span><strong>{{ $scope->name }}</strong><span class="block text-[var(--dash-text-muted)]">{{ $scope->description }}</span></span></label>
                    @empty
                        <p class="text-sm text-[var(--dash-text-muted)]">No active scopes are available.</p>
                    @endforelse
                </fieldset>
                <x-form.button type="submit" size="sm">Continue</x-form.button>
            </form>
        @elseif($step === 'claims')
            <form method="POST" action="{{ route('admin.applications.wizard.claims.store') }}" class="space-y-4 border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                @csrf
                <fieldset class="space-y-3"><legend class="text-sm font-semibold">Claim policy</legend>
                    @forelse($claims as $claim)
                        <label class="flex items-start gap-3 text-sm"><input type="checkbox" name="claim_keys[]" value="{{ $claim->key }}" @checked(in_array($claim->key, data_get($wizard, 'claims.keys', []), true))><span><strong>{{ $claim->key }}</strong><span class="block text-[var(--dash-text-muted)]">{{ $claim->description }}</span></span></label>
                    @empty
                        <p class="text-sm text-[var(--dash-text-muted)]">No active claims are available.</p>
                    @endforelse
                </fieldset>
                <label class="block text-sm font-medium">Policy version<input type="number" min="1" name="claim_policy_version" value="{{ old('claim_policy_version', data_get($wizard, 'claims.version', 1)) }}" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2" required></label>
                <x-form.button type="submit" size="sm">Review</x-form.button>
            </form>
        @elseif($step === 'security')
            <form method="POST" action="{{ route('admin.applications.wizard.security.store') }}" class="space-y-4 border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                @csrf
                <label class="block text-sm font-medium">Consent policy<select name="consent_policy" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2"><option value="explicit">Explicit consent</option><option value="implicit">Implicit consent</option><option value="none">No consent prompt</option></select></label>
                <label class="block text-sm font-medium">Maximum session age (seconds)<input type="number" min="300" max="86400" name="session_max_age" value="{{ old('session_max_age', data_get($wizard, 'security.session_max_age', 3600)) }}" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2" required></label>
                <label class="block text-sm font-medium">Idle timeout (seconds)<input type="number" min="60" max="900" name="session_idle_timeout" value="{{ old('session_idle_timeout', data_get($wizard, 'security.session_idle_timeout', 900)) }}" class="mt-1 block w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2" required></label>
                <x-form.button type="submit" size="sm">Review</x-form.button>
            </form>
        @else
            <div class="space-y-4 border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                <h2 class="text-base font-semibold text-[var(--dash-text-heading)]">Review draft</h2>
                <dl class="space-y-2 text-sm"><div><dt class="text-[var(--dash-text-muted)]">Name</dt><dd>{{ data_get($wizard, 'basic.name') }}</dd></div><div><dt class="text-[var(--dash-text-muted)]">Protocol</dt><dd>{{ strtoupper(data_get($wizard, 'protocol.protocol_mode')) }}</dd></div><div><dt class="text-[var(--dash-text-muted)]">Redirect URI</dt><dd class="break-all">{{ data_get($wizard, 'redirect_uris.0') }}</dd></div><div><dt class="text-[var(--dash-text-muted)]">Scopes</dt><dd>{{ $selectedScopes->pluck('name')->implode(', ') }}</dd></div><div><dt class="text-[var(--dash-text-muted)]">Claims</dt><dd>{{ $selectedClaims->pluck('key')->implode(', ') }}</dd></div><div><dt class="text-[var(--dash-text-muted)]">Consent policy</dt><dd>{{ data_get($wizard, 'security.consent_policy') }}</dd></div><div><dt class="text-[var(--dash-text-muted)]">Session limits</dt><dd>{{ data_get($wizard, 'security.session_max_age') }}s max / {{ data_get($wizard, 'security.session_idle_timeout') }}s idle</dd></div></dl>
                <form method="POST" action="{{ route('admin.applications.wizard.complete') }}">@csrf<x-form.button type="submit" size="sm">Create draft</x-form.button></form>
            </div>
        @endif
    </div>
</x-dashboard.layout>
