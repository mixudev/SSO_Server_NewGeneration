<x-dashboard.layout
    :title="$application->name"
    breadcrumb="Applications / Details"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <x-form.button href="{{ route('admin.applications.index') }}" variant="ghost" size="sm" icon="arrow-left">Back to applications</x-form.button>
                <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">{{ $application->name }}</h1>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">{{ $application->description ?: 'No description provided.' }}</p>
            </div>
            <span class="border border-[var(--dash-border)] px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[var(--dash-text-muted)]">{{ $application->status }}</span>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                <h2 class="text-sm font-semibold text-[var(--dash-text-heading)]">Application details</h2>
                <dl class="mt-4 divide-y divide-[var(--dash-border-subtle)] text-sm">
                    <div class="flex justify-between gap-4 py-3"><dt class="text-[var(--dash-text-muted)]">Slug</dt><dd class="font-medium text-[var(--dash-text)]">{{ $application->slug }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-[var(--dash-text-muted)]">Organization</dt><dd class="font-medium text-[var(--dash-text)]">{{ $application->organization->name }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-[var(--dash-text-muted)]">Protocol</dt><dd class="font-medium uppercase text-[var(--dash-text)]">{{ $application->protocol_mode }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-[var(--dash-text-muted)]">Client type</dt><dd class="font-medium text-[var(--dash-text)]">{{ $application->client_type }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-[var(--dash-text-muted)]">Policy version</dt><dd class="font-medium text-[var(--dash-text)]">{{ $application->claim_policy_version }}</dd></div>
                </dl>
            </section>

            <section class="border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                <h2 class="text-sm font-semibold text-[var(--dash-text-heading)]">Credential boundary</h2>
                <p class="mt-2 text-sm text-[var(--dash-text-muted)]">Secrets are shown once only. Public clients do not receive a secret.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @can('applications.credentials.issue')
                        @if($application->status === 'active' && ! $application->credential)
                            <form method="POST" action="{{ route('admin.applications.credentials.issue', $application) }}">@csrf<x-form.button type="submit" size="sm" icon="key">Issue credential</x-form.button></form>
                        @endif
                    @endcan
                    @can('applications.credentials.rotate')
                        @if($application->credential?->status === 'active')
                            <form method="POST" action="{{ route('admin.applications.credentials.rotate', $application) }}" data-confirm="The previous client secret becomes invalid immediately." data-confirm-type="warning" data-confirm-title="Rotate credential?" data-confirm-btn="Rotate credential">@csrf<x-form.button type="submit" size="sm" variant="secondary" icon="arrow-repeat">Rotate credential</x-form.button></form>
                            <form method="POST" action="{{ route('admin.applications.credentials.revoke', $application) }}" data-confirm="The active client and its tokens will be revoked." data-confirm-type="delete" data-confirm-title="Revoke credential?" data-confirm-btn="Revoke credential">@csrf @method('DELETE')<x-form.button type="submit" size="sm" variant="ghost">Revoke credential</x-form.button></form>
                        @endif
                    @endcan
                </div>
            </section>

            <section class="border border-[var(--dash-border)] bg-[var(--dash-card)] p-5">
                <h2 class="text-sm font-semibold text-[var(--dash-text-heading)]">Redirect URIs</h2>
                @if($application->redirectUris->isEmpty())
                    <p class="mt-4 text-sm text-[var(--dash-text-muted)]">No redirect URI registered.</p>
                @else
                    <ul class="mt-4 space-y-3">
                        @foreach($application->redirectUris as $redirectUri)
                            <li class="break-all border border-[var(--dash-border-subtle)] px-3 py-2 text-sm text-[var(--dash-text)]">{{ $redirectUri->uri }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-dashboard.layout>
