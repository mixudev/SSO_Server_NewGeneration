<x-dashboard.layout
    title="Application portal"
    breadcrumb="Application portal"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <x-ui.page-header
            title="Your applications"
            description="Open an application that has been explicitly assigned to your account."
        />

        @if ($applications->isEmpty())
            <section class="bento-panel">
                <div class="flex items-start gap-4">
                    <div class="bento-icon-mark"><i class="bi bi-grid" aria-hidden="true"></i></div>
                    <div>
                        <h2 class="bento-title">No applications are currently available</h2>
                        <p class="mt-2 text-sm text-[var(--dash-text-muted)]">Contact an administrator if you believe you should have access.</p>
                    </div>
                </div>
            </section>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($applications as $application)
                    <article class="bento-panel flex min-h-52 flex-col justify-between gap-6">
                        <div>
                            <span class="bento-kicker">{{ $application->organization->name }}</span>
                            <h2 class="mt-3 text-xl font-semibold text-[var(--dash-text-heading)]">{{ $application->name }}</h2>
                            <p class="mt-2 text-sm leading-6 text-[var(--dash-text-muted)]">{{ $application->description ?: 'Assigned application.' }}</p>
                        </div>
                        <div>
                            <a href="{{ $application->homepage_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 border border-[var(--dash-border)] px-3 py-2 text-xs font-semibold text-[var(--dash-text-heading)] hover:border-[var(--dash-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--dash-primary)]">
                                Open application
                                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-dashboard.layout>
