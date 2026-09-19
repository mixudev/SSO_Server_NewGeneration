<x-dashboard.layout
    :title="$application->name"
    breadcrumb="Applications / Details"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <x-ui.page-header
            :back-url="route('admin.applications.index')"
            back-label="Back to applications"
            :title="$application->name"
            :description="$application->description ?: 'Identity client control surface and protocol configuration.'"
        >
            <x-slot:actions>
                <x-status-badge :value="$application->status" />
                @can('applications.update')
                    <x-form.button type="button" variant="secondary" size="sm" icon="pencil-square" onclick="AppModal.open('edit-application-modal')">Edit client</x-form.button>
                @endcan
            </x-slot:actions>
        </x-ui.page-header>

        <div class="bento-grid bento-grid-application">
            <section class="bento-panel bento-panel-hero">
                <div class="bento-orbit" aria-hidden="true"></div>
                <div class="relative z-10 flex h-full flex-col justify-between gap-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="bento-kicker">Application identity</span>
                            <h2 class="mt-3 text-3xl font-light tracking-tight text-[var(--dash-text-heading)]">{{ $application->name }}</h2>
                            <p class="mt-2 max-w-xl text-sm text-[var(--dash-text-muted)]">{{ $application->slug }} · {{ str($application->client_type)->replace('_', ' ')->title() }}</p>
                        </div>
                        <div class="bento-icon-mark"><i class="bi bi-shield-lock" aria-hidden="true"></i></div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="bento-stat"><span>Protocol</span><strong>{{ strtoupper($application->protocol_mode) }}</strong></div>
                        <div class="bento-stat"><span>Scopes</span><strong>{{ $application->scopes->count() }}</strong></div>
                        <div class="bento-stat"><span>Redirects</span><strong>{{ $application->redirectUris->count() }}</strong></div>
                    </div>
                    <div class="bento-identity-lower grid gap-4 lg:grid-cols-2">
                        <div>
                            <span class="bento-kicker">Client requirements</span>
                            <p class="mt-2 text-xs leading-5 text-[var(--dash-text-muted)]">
                                @if($application->client_type === 'confidential_web')
                                    Server-side web client: secret required, PKCE recommended, exact HTTPS redirect URI in production.
                                @elseif($application->client_type === 'public_spa')
                                    Browser SPA: no secret, PKCE S256 mandatory, exact HTTPS redirect URI in production.
                                @else
                                    Native client: no secret, PKCE S256 mandatory, loopback or claimed HTTPS redirect URI.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bento-panel bento-panel-credential">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="bento-kicker">Credential access</span>
                        <h2 class="bento-title">Client credentials</h2>
                    </div>
                    <i class="bi bi-key text-xl text-[var(--dash-primary)]" aria-hidden="true"></i>
                </div>

                @if($application->status === 'draft')
                    <p class="mt-4 text-[11px] leading-relaxed text-[var(--dash-text-muted)]">Draft application. Activate it here before generating credentials.</p>
                @else
                    <p class="mt-4 text-[11px] leading-relaxed text-[var(--dash-text-muted)]">Secrets are shown once. Public clients do not have a secret to regenerate.</p>
                @endif

                <div class="mt-6 flex items-center justify-between border-y border-[var(--dash-border-subtle)] py-4">
                    <div>
                        <p class="text-xs text-[var(--dash-text-muted)]">Credential status</p>
                        <p class="mt-1 font-mono text-sm text-[var(--dash-text-heading)]">{{ $application->credential ? str($application->credential->status)->upper() : 'NOT ISSUED' }}</p>
                    </div>
                    <span class="bento-pulse-dot {{ $application->credential?->status === 'active' ? 'is-active' : '' }}"></span>
                </div>

                @if($application->credential?->status === 'active')
                    <div class="bento-endpoint mt-4">
                        <span>Client ID</span>
                        <code title="{{ $application->credential->passport_client_id }}">{{ $application->credential->passport_client_id }}</code>
                        <button type="button" class="bento-copy" data-copy="{{ $application->credential->passport_client_id }}" aria-label="Copy client ID"><i class="bi bi-copy" aria-hidden="true"></i></button>
                    </div>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    @if($application->status === 'draft')
                        @can('applications.update')
                            <form method="POST" action="{{ route('admin.applications.activate', $application) }}">
                                @csrf
                                <x-form.button type="submit" size="sm" icon="check2-circle">Activate application</x-form.button>
                            </form>
                        @endcan
                    @elseif($application->credential?->status === 'revoked')
                        @can('applications.credentials.issue')
                            <x-form.button type="button" size="xs" icon="key" onclick="AppModal.open('reactivate-client-modal')">Activate</x-form.button>
                        @endcan
                    @elseif(! $application->credential)
                        @can('applications.credentials.issue')
                            <x-form.button type="button" size="sm" icon="key" onclick="AppModal.open('issue-client-modal')">Generate credentials</x-form.button>
                        @endcan
                    @endif

                    @can('applications.credentials.rotate')
                        @if($application->credential?->status === 'active')
                            @if($application->client_type === 'confidential_web')
                                <x-form.button type="button" size="xs" variant="secondary" icon="arrow-repeat" onclick="AppModal.open('rotate-client-modal')">Regenerate</x-form.button>
                            @endif
                            <x-form.button type="button" size="xs" variant="danger" icon="shield-x" onclick="AppModal.open('revoke-client-modal')">Revoke</x-form.button>
                        @endif
                    @endcan
                </div>

            </section>

            <section class="bento-panel bento-panel-wide">
                <div class="flex items-start justify-between gap-4">
                    <div><span class="bento-kicker">{{ $application->protocol_mode === 'oidc' ? 'OpenID Connect provider' : 'OAuth 2.0 provider' }}</span><h2 class="bento-title">{{ $application->protocol_mode === 'oidc' ? 'Issuer & OIDC endpoints' : 'Authorization & token endpoints' }}</h2><p class="mt-1 text-xs text-[var(--dash-text-muted)]">{{ $application->protocol_mode === 'oidc' ? 'Discovery, JWKS, authorization, token, and UserInfo configuration.' : 'Authorization code, PKCE, token, refresh, and revocation configuration.' }}</p></div>
                    <form method="POST" action="{{ route('admin.applications.connection-test', $application) }}">
                        @csrf
                        <x-form.button type="submit" variant="secondary" size="sm" icon="activity">Test connection</x-form.button>
                    </form>
                </div>
                <div class="mt-6 grid gap-3 md:grid-cols-2">
                    @foreach([
                        'Issuer URL' => rtrim(config('app.url'), '/'),
                        'Authorization endpoint' => rtrim(config('app.url'), '/').'/oauth/authorize',
                        'Token endpoint' => rtrim(config('app.url'), '/').'/oauth/token',
                        ...($application->protocol_mode === 'oidc' ? [
                            'Discovery document' => rtrim(config('app.url'), '/').'/.well-known/openid-configuration',
                            'JWKS endpoint' => rtrim(config('app.url'), '/').'/.well-known/jwks.json',
                            'UserInfo endpoint' => rtrim(config('app.url'), '/').'/oauth/userinfo',
                        ] : [
                            'Revocation endpoint' => rtrim(config('app.url'), '/').'/oauth/revoke',
                        ]),
                    ] as $label => $value)
                        <div class="bento-endpoint"><span>{{ $label }}</span><code title="{{ $value }}">{{ $value }}</code><button type="button" class="bento-copy" data-copy="{{ $value }}" aria-label="Copy {{ $label }}"><i class="bi bi-copy" aria-hidden="true"></i></button></div>
                    @endforeach
                </div>
            </section>

            <section class="bento-panel">
                <span class="bento-kicker">Organization boundary</span>
                <h2 class="bento-title">{{ $application->organization->name }}</h2>
                <p class="mt-2 text-xs text-[var(--dash-text-muted)]">{{ $application->organization->slug }}</p>
                <a class="bento-link mt-6" href="{{ route('admin.organizations.show', $application->organization) }}">View organization <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </section>

            <section class="bento-panel">
                <span class="bento-kicker">Security posture</span>
                <h2 class="bento-title">{{ $signingKeyReady ? 'Ready for protocol traffic' : 'Signing key required' }}</h2>
                <div class="mt-4 space-y-2 text-xs text-[var(--dash-text-muted)]">
                    <p><i class="bi bi-check2-circle mr-2 text-[var(--dash-success)]"></i>PKCE S256 enforced</p>
                    <p><i class="bi bi-check2-circle mr-2 text-[var(--dash-success)]"></i>Exact redirect matching</p>
                    <p><i class="bi bi-check2-circle mr-2 text-[var(--dash-success)]"></i>Organization lifecycle enforced</p>
                </div>
            </section>

            <section class="bento-panel bento-panel-wide">
                <div class="flex items-center justify-between gap-4"><div><span class="bento-kicker">Allowed callbacks</span><h2 class="bento-title">Redirect URIs</h2></div><span class="font-mono text-xs text-[var(--dash-text-muted)]">{{ $application->redirectUris->count() }} registered</span></div>
                <div class="mt-5 grid gap-2">
                    @forelse($application->redirectUris as $redirectUri)
                        <div class="bento-endpoint"><code>{{ $redirectUri->uri }}</code><i class="bi bi-check-circle text-[var(--dash-success)]" aria-label="Validated redirect URI"></i></div>
                    @empty
                        <p class="text-xs text-[var(--dash-text-muted)]">No redirect URI registered.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    @can('applications.update')
        <x-app-modal id="edit-application-modal" maxWidth="xl" title="Edit OAuth client" description="Update client metadata and validated redirect URIs." icon="pencil-square">
            <form id="edit-application-form" method="POST" action="{{ route('admin.applications.update', $application) }}" class="grid gap-4">
                @csrf @method('PUT')
                <x-form.input name="name" label="Application name" :value="$application->name" required />
                <p class="-mt-3 text-[11px] text-[var(--dash-text-muted)]">Slug: <span class="font-mono text-[var(--dash-text-heading)]">{{ $application->slug }}</span> · generated automatically by the system</p>
                <input type="hidden" name="slug" value="{{ $application->slug }}" />

                <x-form.select name="organization_id" label="Organization" required>@foreach($organizations as $organization)<option value="{{ $organization->getKey() }}" @selected($application->organization_id === $organization->getKey())>{{ $organization->name }} ({{ $organization->slug }})</option>@endforeach</x-form.select>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="mb-1.5 block font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium uppercase tracking-[0.02em] text-[var(--dash-muted)]">Protocol</p>
                        <p class="rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-bg)] px-3 py-2 text-xs text-[var(--dash-text-heading)]">{{ $application->protocol_mode === 'oidc' ? 'OpenID Connect' : 'OAuth 2.0' }}</p>
                        <input type="hidden" name="protocol_mode" value="{{ $application->protocol_mode }}" />
                    </div>
                    <div>
                        <p class="mb-1.5 block font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium uppercase tracking-[0.02em] text-[var(--dash-muted)]">Client type</p>
                        <p class="rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-bg)] px-3 py-2 text-xs text-[var(--dash-text-heading)]">{{ str($application->client_type)->replace('_', ' ')->title() }}</p>
                        <input type="hidden" name="client_type" value="{{ $application->client_type }}" />
                    </div>
                </div>
                <p class="-mt-2 text-[11px] text-[var(--dash-text-muted)]">Protocol and client type cannot be changed after application creation.</p>

                <div>
                    <p class="mb-2 font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium uppercase tracking-[0.02em] text-[var(--dash-muted)]">Login redirect URIs</p>
                    @foreach($application->redirectUris->where('kind', 'login') as $redirectUri)
                        <input name="redirect_uris[]" value="{{ $redirectUri->uri }}" required class="mb-2 w-full rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] px-3 py-2 text-xs text-[var(--dash-text)]" />
                    @endforeach
                </div>

                <div>
                    <p class="mb-2 font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium uppercase tracking-[0.02em] text-[var(--dash-muted)]">Post-logout redirect URIs</p>
                    @foreach($application->redirectUris->where('kind', 'logout') as $redirectUri)
                        <input name="logout_redirect_uris[]" value="{{ $redirectUri->uri }}" class="mb-2 w-full rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] px-3 py-2 text-xs text-[var(--dash-text)]" />
                    @endforeach
                    <input name="logout_redirect_uris[]" value="" placeholder="https://client.example/logout/callback" class="w-full rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] px-3 py-2 text-xs text-[var(--dash-text)]" />
                </div>

                <x-form.textarea name="description" label="Description" :value="$application->description" rows="4" />
            </form>
            <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('edit-application-modal')">Cancel</x-form.button><x-form.button type="submit" form="edit-application-form">Save client</x-form.button></x-slot>
        </x-app-modal>
    @endcan

    @can('applications.credentials.issue')
        <x-app-modal id="issue-client-modal" maxWidth="md" title="Generate application credentials" description="The client secret is displayed once after generation." icon="key">
            <p class="text-sm text-[var(--dash-text-muted)]">Generate a Passport client ID and, for confidential applications, a new client secret.</p>
            <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('issue-client-modal')">Cancel</x-form.button><form method="POST" action="{{ route('admin.applications.credentials.issue', $application) }}">@csrf<x-form.button type="submit">Generate credentials</x-form.button></form></x-slot>
        </x-app-modal>
    @endcan

    @can('applications.credentials.issue')
        @if($application->credential?->status === 'revoked')
            <x-app-modal id="reactivate-client-modal" maxWidth="md" title="Activate credentials" description="A new client credential will be issued." icon="key">
                <p class="text-sm text-[var(--dash-text-muted)]">The revoked credential will remain invalid. A new client ID and, for confidential clients, a new secret will be shown once.</p>
                <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('reactivate-client-modal')">Cancel</x-form.button><form method="POST" action="{{ route('admin.applications.credentials.reactivate', $application) }}">@csrf<x-form.button type="submit">Activate</x-form.button></form></x-slot>
            </x-app-modal>
        @endif
    @endcan

    @can('applications.credentials.rotate')
        <x-app-modal id="rotate-client-modal" maxWidth="md" title="Regenerate client secret" description="The previous secret becomes invalid immediately." icon="arrow-repeat" iconColor="amber">
            <p class="text-sm text-[var(--dash-text-muted)]">Update the consuming application immediately after this operation. The new secret is shown once.</p>
            <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('rotate-client-modal')">Cancel</x-form.button><form method="POST" action="{{ route('admin.applications.credentials.rotate', $application) }}">@csrf<x-form.button type="submit" variant="secondary">Regenerate secret</x-form.button></form></x-slot>
        </x-app-modal>
        <x-app-modal id="revoke-client-modal" maxWidth="md" title="Revoke application credentials" description="The client and its tokens will be revoked." icon="shield-x" iconColor="red">
            <p class="text-sm text-[var(--dash-text-muted)]">This stops new protocol requests from this application. Continue only if the client must be disabled.</p>
            <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('revoke-client-modal')">Cancel</x-form.button><form method="POST" action="{{ route('admin.applications.credentials.revoke', $application) }}">@csrf @method('DELETE')<x-form.button type="submit" variant="danger">Revoke credentials</x-form.button></form></x-slot>
        </x-app-modal>
    @endcan
</x-dashboard.layout>
