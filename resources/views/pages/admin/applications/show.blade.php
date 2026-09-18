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
