<x-dashboard.layout
    title="User activity"
    breadcrumb="Users & Roles / Activity"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <div>
            <x-form.button href="{{ route('admin.users.show', $user) }}" variant="ghost" size="sm" icon="arrow-left">Back to user</x-form.button>
            <p class="mt-5 text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Security trail</p>
            <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">All activity</h1>
            <p class="mt-1 text-sm text-[var(--dash-text-muted)]">{{ $user->name }} · {{ $user->email }}</p>
        </div>
        <section class="bento-card bento-card-animated">
            <x-table.wrapper :striped="true">
                <x-slot:head><x-table.th>Event</x-table.th><x-table.th>Risk</x-table.th><x-table.th>When</x-table.th></x-slot:head>
                @forelse ($activities as $activity)
                    <tr><x-table.td><span class="font-medium text-[var(--dash-text-heading)]">{{ $activity->event }}</span><div class="text-xs text-[var(--dash-text-muted)]">{{ $activity->actor === (string) $user->uuid ? 'Actor' : 'Subject' }}</div></x-table.td><x-table.td><x-ui.badge :variant="$activity->risk === 'critical' || $activity->risk === 'high' ? 'danger' : 'neutral'" :pill="true">{{ $activity->risk }}</x-ui.badge></x-table.td><x-table.td class="text-sm text-[var(--dash-text-muted)]">{{ $activity->occurred_at?->format('d M Y, H:i') }}</x-table.td></tr>
                @empty
                    <tr><x-table.td colspan="3"><span class="text-sm text-[var(--dash-text-muted)]">No security activity recorded.</span></x-table.td></tr>
                @endforelse
            </x-table.wrapper>
            <div class="mt-5"><x-ui.pagination :paginator="$activities" /></div>
        </section>
    </div>
</x-dashboard.layout>
