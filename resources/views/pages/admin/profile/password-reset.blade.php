<x-dashboard.layout
    title="Password reset"
    breadcrumb="Account / Security / Password reset"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="mx-auto max-w-xl space-y-6">
        <div><a href="{{ route('admin.profile.security') }}" class="text-xs font-semibold text-[var(--dash-primary)]"><i class="bi bi-arrow-left mr-1" aria-hidden="true"></i>Back to security center</a><h1 class="mt-4 text-2xl font-semibold text-[var(--dash-text-heading)]">Password recovery</h1><p class="mt-1 text-sm text-[var(--dash-text-muted)]">Password actions are available from the security center. Use the modal there to change your password or send a secure link.</p></div>
        <section class="border border-[var(--dash-border)] bg-[var(--dash-card)]">
            <header class="border-b border-[var(--dash-border)] px-6 py-5"><p class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--dash-primary)]">Canonical action</p><h2 class="mt-1 text-lg font-semibold text-[var(--dash-text-heading)]">Manage password</h2></header>
            <div class="px-6 py-6"><p class="text-sm text-[var(--dash-text-muted)]">Keep password change and reset-link actions in one place to avoid duplicate flows.</p><a href="{{ route('admin.profile.show') }}" class="mt-5 inline-flex items-center gap-2 bg-[var(--dash-primary)] px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90">Open profile <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
        </section>
    </div>
</x-dashboard.layout>
