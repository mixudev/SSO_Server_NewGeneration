<x-dashboard.layout title="Session inspector" breadcrumb="Session inspector" :user-name="auth()->user()->name" :user-email="auth()->user()->email" :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--dash-primary)]">Access control</p>
            <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">Active sessions</h1>
            <p class="mt-2 text-sm text-[var(--dash-text-muted)]">Read-only inventory of active database-backed sessions. Session payloads and identifiers are never displayed.</p>
        </div>
        <section class="overflow-hidden border border-[var(--dash-border)] bg-[var(--dash-card)]">
            @if ($sessions->isEmpty())
                <div class="p-8 text-center text-sm text-[var(--dash-text-muted)]">No active database sessions were found.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-[var(--dash-border)] text-xs uppercase tracking-wide text-[var(--dash-text-muted)]">
                            <tr><th class="px-5 py-3">User</th><th class="px-5 py-3">Network</th><th class="px-5 py-3">Browser</th><th class="px-5 py-3">Last activity</th></tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--dash-border)]">
                            @foreach ($sessions as $session)
                                <tr>
                                    <td class="px-5 py-4"><div class="font-medium text-[var(--dash-text-heading)]">{{ $session->user_name }}</div><div class="text-xs text-[var(--dash-text-muted)]">{{ $session->user_email }}</div></td>
                                    <td class="px-5 py-4 text-[var(--dash-text-muted)]">{{ $session->ip_address ? preg_replace('/(?<=\.).*(?=\.)/', '…', $session->ip_address) : 'Unknown' }}</td>
                                    <td class="max-w-xs truncate px-5 py-4 text-[var(--dash-text-muted)]" title="{{ $session->user_agent }}">{{ $session->user_agent ?: 'Unknown' }}</td>
                                    <td class="px-5 py-4 text-[var(--dash-text-muted)]">{{ \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-[var(--dash-border)] p-4">{{ $sessions->links() }}</div>
            @endif
        </section>
    </div>
</x-dashboard.layout>
