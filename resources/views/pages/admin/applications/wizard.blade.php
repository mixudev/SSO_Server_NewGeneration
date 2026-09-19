<x-dashboard.layout
    title="Create application"
    breadcrumb="Applications / Create"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="mx-auto max-w-3xl space-y-6">
        <x-ui.page-header
            :back-url="route('admin.applications.index')"
            back-label="Cancel and exit"
            kicker="Application wizard"
            title="Register client application"
            description="Complete each server-validated step before finalizing the draft application."
        />

        {{-- Wizard Step Indicator --}}
        @php
            $steps = [
                'basic'    => 'Basic',
                'protocol' => 'Protocol',
                'redirect' => 'Redirects',
                'scopes'   => 'Scopes',
                'claims'   => 'Claims',
                'security' => 'Security',
                'review'   => 'Review',
            ];
            $stepKeys = array_keys($steps);
            $currentStepIndex = array_search($step, $stepKeys);
        @endphp

        <div class="overflow-x-auto pb-1">
            <div class="flex min-w-[540px] items-center justify-between border border-[var(--dash-border)] bg-[var(--dash-card)] px-4 py-3 rounded-[var(--dash-radius)]">
                @foreach($steps as $key => $label)
                    @php
                        $index = array_search($key, $stepKeys);
                        $isCurrent = $key === $step;
                        $isPast = $currentStepIndex !== false && $index < $currentStepIndex;
                    @endphp
                    <div class="flex items-center gap-2">
                        <span @class([
                            'flex h-6 w-6 items-center justify-center font-[var(--font-ppneuemontrealmono)] text-[10px] font-semibold rounded-[var(--dash-radius)]',
                            'bg-[var(--dash-primary)] text-white' => $isCurrent,
                            'bg-[var(--dash-primary-soft)] text-[var(--dash-primary)]' => $isPast,
                            'bg-[var(--dash-card-hover)] text-[var(--dash-muted)] border border-[var(--dash-border)]' => ! $isCurrent && ! $isPast,
                        ])>
                            @if($isPast)
                                <i class="bi bi-check" aria-hidden="true"></i>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </span>
                        <span @class([
                            'text-xs font-medium',
                            'text-[var(--dash-primary)] font-semibold' => $isCurrent,
                            'text-[var(--dash-text-heading)]' => $isPast,
                            'text-[var(--dash-muted)]' => ! $isCurrent && ! $isPast,
                        ])>
                            {{ $label }}
                        </span>
                    </div>
                    @if(! $loop->last)
                        <div class="h-px flex-1 bg-[var(--dash-border)] mx-2"></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Step: Basic --}}
        @if($step === 'basic')
            <x-ui.card title="Basic information" kicker="Step 01 / 07">
                <form method="POST" action="{{ route('admin.applications.wizard.basic.store') }}" class="space-y-4">
                    @csrf
                    <x-form.select name="organization_id" label="Organization" required>
                        <option value="">Select an active organization</option>
                        @foreach($organizations as $organization)
                            <option value="{{ $organization->getKey() }}" @selected((string) old('organization_id', data_get($wizard, 'basic.organization_id')) === (string) $organization->getKey())>
                                {{ $organization->name }} ({{ $organization->slug }})
                            </option>
                        @endforeach
                    </x-form.select>
                    @if($organizations->isEmpty())
                        <p class="text-xs text-[var(--dash-danger)]">Create an active organization before registering an application.</p>
                    @endif

                    <x-form.input
                        name="name"
                        label="Application name"
                        :value="old('name', data_get($wizard, 'basic.name'))"
                        required
                        placeholder="e.g. Customer Portal"
                    />

                    <x-form.input
                        name="slug"
                        label="Application slug"
                        :value="old('slug', data_get($wizard, 'basic.slug'))"
                        required
                        placeholder="e.g. customer-portal"
                        help="Unique lowercase identifier."
                    />

                    <x-form.textarea
                        name="description"
                        label="Description"
                        :value="old('description', data_get($wizard, 'basic.description'))"
                        placeholder="Optional purpose of this application..."
                    />

                    <div class="flex justify-end border-t border-[var(--dash-border)] pt-4">
                        <x-form.button type="submit" size="md" iconRight="arrow-right" :disabled="$organizations->isEmpty()">
                            Continue to protocol
                        </x-form.button>
                    </div>
                </form>
            </x-ui.card>

        {{-- Step: Protocol --}}
        @elseif($step === 'protocol')
            <x-ui.card title="Protocol mode & client type" kicker="Step 02 / 07">
                <form method="POST" action="{{ route('admin.applications.wizard.protocol.store') }}" class="space-y-4">
                    @csrf
                    <x-form.select name="protocol_mode" label="Protocol mode" required>
                        <option value="oauth2" @selected(data_get($wizard, 'protocol.protocol_mode') === 'oauth2')>OAuth 2.0</option>
                        <option value="oidc" @selected(data_get($wizard, 'protocol.protocol_mode') === 'oidc')>OpenID Connect (OIDC)</option>
                    </x-form.select>

                    <x-form.select name="client_type" label="Client type" required>
                        <option value="confidential_web" @selected(data_get($wizard, 'protocol.client_type') === 'confidential_web')>Confidential Web (Server-side application with client secret)</option>
                        <option value="public_spa" @selected(data_get($wizard, 'protocol.client_type') === 'public_spa')>Public SPA (Browser-based Single Page App with PKCE)</option>
                        <option value="native" @selected(data_get($wizard, 'protocol.client_type') === 'native')>Native / Mobile App</option>
                    </x-form.select>

                    <div class="flex justify-end border-t border-[var(--dash-border)] pt-4">
                        <x-form.button type="submit" size="md" iconRight="arrow-right">
                            Continue to redirects
                        </x-form.button>
                    </div>
                </form>
            </x-ui.card>

        {{-- Step: Redirect URIs --}}
        @elseif($step === 'redirect')
            <x-ui.card title="Allowed redirect URIs" kicker="Step 03 / 07" description="Must be an absolute HTTPS URL or localhost callback.">
                <form method="POST" action="{{ route('admin.applications.wizard.redirect.store') }}" class="space-y-4">
                    @csrf
                    <x-form.input
                        name="redirect_uris[]"
                        label="Callback URI"
                        :value="old('redirect_uris.0', data_get($wizard, 'redirect_uris.0'))"
                        placeholder="https://client.example/callback"
                        required
                    />

                    <div class="flex justify-end border-t border-[var(--dash-border)] pt-4">
                        <x-form.button type="submit" size="md" iconRight="arrow-right">
                            Continue to scopes
                        </x-form.button>
                    </div>
                </form>
            </x-ui.card>

        {{-- Step: Scopes --}}
        @elseif($step === 'scopes')
            <x-ui.card title="Assign allowed scopes" kicker="Step 04 / 07">
                <form method="POST" action="{{ route('admin.applications.wizard.scopes.store') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3 divide-y divide-[var(--dash-border-subtle)]">
                        @forelse($scopes as $scope)
                            <div class="pt-3 first:pt-0">
                                <x-form.checkbox
                                    name="scope_ids[]"
                                    :value="$scope->id"
                                    :checked="in_array($scope->id, data_get($wizard, 'scope_ids', []), true)"
                                    :label="$scope->name"
                                    :description="$scope->description"
                                />
                            </div>
                        @empty
                            <p class="text-xs text-[var(--dash-text-muted)]">No active scopes are available in registry.</p>
                        @endforelse
                    </div>

                    <div class="flex justify-end border-t border-[var(--dash-border)] pt-4">
                        <x-form.button type="submit" size="md" iconRight="arrow-right">
                            Continue to claims
                        </x-form.button>
                    </div>
                </form>
            </x-ui.card>

        {{-- Step: Claims --}}
        @elseif($step === 'claims')
            <x-ui.card title="Claim policy configuration" kicker="Step 05 / 07">
                <form method="POST" action="{{ route('admin.applications.wizard.claims.store') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3 divide-y divide-[var(--dash-border-subtle)]">
                        @forelse($claims as $claim)
                            <div class="pt-3 first:pt-0">
                                <x-form.checkbox
                                    name="claim_keys[]"
                                    :value="$claim->key"
                                    :checked="in_array($claim->key, data_get($wizard, 'claims.keys', []), true)"
                                    :label="$claim->key"
                                    :description="$claim->description"
                                />
                            </div>
                        @empty
                            <p class="text-xs text-[var(--dash-text-muted)]">No active claims are available in registry.</p>
                        @endforelse
                    </div>

                    <div class="rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card-hover)] px-3 py-2.5 text-xs text-[var(--dash-text-muted)]">
                        The claim policy starts at version 1. The selected active claims define this policy; versioning is managed by the server.
                    </div>

                    <div class="flex justify-end border-t border-[var(--dash-border)] pt-4">
                        <x-form.button type="submit" size="md" iconRight="arrow-right">
                            Continue to security
                        </x-form.button>
                    </div>
                </form>
            </x-ui.card>

        {{-- Step: Security --}}
        @elseif($step === 'security')
            <x-ui.card title="Session & consent policy" kicker="Step 06 / 07">
                <form method="POST" action="{{ route('admin.applications.wizard.security.store') }}" class="space-y-4">
                    @csrf
                    <x-form.select name="consent_policy" label="Consent policy" required>
                        <option value="explicit" @selected(data_get($wizard, 'security.consent_policy') === 'explicit')>Explicit consent (User must grant access)</option>
                        <option value="implicit" @selected(data_get($wizard, 'security.consent_policy') === 'implicit')>Implicit consent (First-party application)</option>
                        <option value="none" @selected(data_get($wizard, 'security.consent_policy') === 'none')>No consent prompt</option>
                    </x-form.select>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-form.input
                            type="number"
                            min="300"
                            max="86400"
                            name="session_max_age"
                            label="Maximum session age (seconds)"
                            :value="old('session_max_age', data_get($wizard, 'security.session_max_age', 3600))"
                            required
                        />

                        <x-form.input
                            type="number"
                            min="60"
                            max="900"
                            name="session_idle_timeout"
                            label="Idle timeout (seconds)"
                            :value="old('session_idle_timeout', data_get($wizard, 'security.session_idle_timeout', 900))"
                            required
                        />
                    </div>

                    <div class="flex justify-end border-t border-[var(--dash-border)] pt-4">
                        <x-form.button type="submit" size="md" iconRight="arrow-right">
                            Review application
                        </x-form.button>
                    </div>
                </form>
            </x-ui.card>

        {{-- Step: Review --}}
        @else
            <x-ui.card title="Review draft registration" kicker="Step 07 / 07" description="Verify all client parameters before creating the application record.">
                <dl class="divide-y divide-[var(--dash-border-subtle)] text-xs">
                    <div class="flex justify-between py-2.5">
                        <dt class="text-[var(--dash-text-muted)]">Application Name</dt>
                        <dd class="font-medium text-[var(--dash-text-heading)]">{{ data_get($wizard, 'basic.name') }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-[var(--dash-text-muted)]">Protocol Mode</dt>
                        <dd class="font-[var(--font-ppneuemontrealmono)] font-medium uppercase text-[var(--dash-text-heading)]">{{ data_get($wizard, 'protocol.protocol_mode') }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-[var(--dash-text-muted)]">Redirect URI</dt>
                        <dd class="font-[var(--font-ppneuemontrealmono)] break-all text-[var(--dash-text-heading)]">{{ data_get($wizard, 'redirect_uris.0') }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-[var(--dash-text-muted)]">Selected Scopes</dt>
                        <dd class="text-[var(--dash-text-heading)]">{{ $selectedScopes->pluck('name')->implode(', ') ?: 'None' }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-[var(--dash-text-muted)]">Selected Claims</dt>
                        <dd class="text-[var(--dash-text-heading)]">{{ $selectedClaims->pluck('key')->implode(', ') ?: 'None' }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-[var(--dash-text-muted)]">Consent Policy</dt>
                        <dd class="font-medium text-[var(--dash-text-heading)]">{{ data_get($wizard, 'security.consent_policy') }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-[var(--dash-text-muted)]">Session Timeouts</dt>
                        <dd class="font-[var(--font-ppneuemontrealmono)] text-[var(--dash-text-heading)]">{{ data_get($wizard, 'security.session_max_age') }}s max / {{ data_get($wizard, 'security.session_idle_timeout') }}s idle</dd>
                    </div>
                </dl>

                <div class="mt-6 flex justify-end border-t border-[var(--dash-border)] pt-4">
                    <form method="POST" action="{{ route('admin.applications.wizard.complete') }}">
                        @csrf
                        <x-form.button type="submit" size="md" icon="check2">
                            Create draft application
                        </x-form.button>
                    </form>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-dashboard.layout>
