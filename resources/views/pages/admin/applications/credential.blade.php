<x-dashboard.layout title="Credential {{ ucfirst($operation) }}" breadcrumb="Applications / Credential" :user-name="auth()->user()->name" :user-email="auth()->user()->email" :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="mx-auto max-w-2xl space-y-6">
        <header><p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--dash-primary)]">One-time secret</p><h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">Credential {{ ucfirst($operation) }}</h1><p class="mt-1 text-sm text-[var(--dash-text-muted)]">{{ $application->name }}. Store the secret securely; it will not be shown again.</p></header>
        <section class="space-y-4 border border-[var(--dash-border)] bg-[var(--dash-card)] p-6">
            <div><label class="text-xs font-semibold text-[var(--dash-text-muted)]">Client ID</label><p class="mt-1 break-all font-mono text-sm text-[var(--dash-text-heading)]">{{ $result['client_id'] }}</p></div>
            @if($result['client_secret'])<div><label class="text-xs font-semibold text-[var(--dash-text-muted)]">Client secret</label><p class="mt-1 break-all font-mono text-sm text-[var(--dash-text-heading)]">{{ $result['client_secret'] }}</p></div>@else<p class="text-sm text-[var(--dash-text-muted)]">This public client does not use a client secret.</p>@endif
            <p class="text-xs text-[var(--dash-text-muted)]">Generation {{ $result['generation'] }}. This page contains the secret only for this response.</p>
        </section>
        <x-form.button href="{{ route('admin.applications.show', $application) }}" variant="secondary" size="sm">Back to application</x-form.button>
    </div>
</x-dashboard.layout>
