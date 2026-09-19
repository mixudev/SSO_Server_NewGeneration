<x-dashboard.layout title="Session inspector" breadcrumb="Session inspector" :user-name="auth()->user()->name" :user-email="auth()->user()->email" :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="space-y-6">
        <x-ui.page-header kicker="Access control" title="Active sessions" description="A live inventory of every authenticated database session across the control plane. Payloads and session identifiers remain private." />

        <div class="grid gap-4 md:grid-cols-3">
            <div class="bento-stat"><span>Active sessions</span><strong>{{ $sessions->total() }}</strong></div>
            <div class="bento-stat"><span>Showing page</span><strong>{{ $sessions->currentPage() }} / {{ $sessions->lastPage() }}</strong></div>
            <div class="bento-stat"><span>Scope</span><strong>All users</strong></div>
        </div>

        <section class="bento-panel bento-panel-full !min-h-0 !p-0">
            <div class="flex flex-col gap-2 border-b border-[var(--dash-border)] px-6 py-5 sm:flex-row sm:items-center sm:justify-between"><div><span class="bento-kicker">Session activity</span><h2 class="bento-title">Currently connected identities</h2></div><span class="text-xs text-[var(--dash-text-muted)]">Expired sessions excluded automatically</span></div>
            @if ($sessions->isEmpty())
                <div class="p-10 text-center text-sm text-[var(--dash-text-muted)]">No active database sessions were found.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-[var(--dash-border)] text-xs uppercase tracking-wide text-[var(--dash-text-muted)]"><tr><th class="px-6 py-3">Identity</th><th class="px-6 py-3">Network</th><th class="px-6 py-3">Client</th><th class="px-6 py-3">Last activity</th><th class="px-6 py-3">State</th></tr></thead>
                        <tbody class="divide-y divide-[var(--dash-border)]">
                            @foreach ($sessions as $session)
                                <tr class="transition-colors hover:bg-[var(--dash-card-hover)]">
                                    <td class="px-6 py-4"><div class="flex items-center gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-[var(--dash-primary-soft)] font-semibold text-[var(--dash-primary)]">{{ strtoupper(mb_substr($session->user_name, 0, 1)) }}</span><div><div class="font-medium text-[var(--dash-text-heading)]">{{ $session->user_name }}</div><div class="text-xs text-[var(--dash-text-muted)]">{{ $session->user_email }}</div></div></div></td>
                                    <td class="px-6 py-4 font-mono text-xs text-[var(--dash-text-muted)]">{{ $session->ip_address ? preg_replace('/(?<=\.).*(?=\.)/', '…', $session->ip_address) : 'Unknown' }}</td>
                                    <td class="max-w-xs px-6 py-4"><div class="truncate text-[var(--dash-text-muted)]" title="{{ $session->user_agent }}">{{ $session->user_agent ?: 'Unknown' }}</div><div class="mt-1 text-xs text-[var(--dash-muted)]">Browser / device</div></td>
                                    <td class="px-6 py-4 text-[var(--dash-text-muted)]">{{ \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</td>
                                    <td class="px-6 py-4"><x-status-badge value="active" /></td>
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