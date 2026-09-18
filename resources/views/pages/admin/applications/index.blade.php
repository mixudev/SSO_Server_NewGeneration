<x-dashboard.layout
    title="Applications"
    breadcrumb="Applications"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--dash-primary)]">Identity resources</p>
                <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">Applications</h1>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Manage registered client applications and their lifecycle state.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.applications.index') }}" class="grid gap-3 rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] p-4 md:grid-cols-[1fr_180px_auto]">
            <label class="sr-only" for="application-search">Search applications</label>
            <input id="application-search" name="search" value="{{ $search }}" placeholder="Search name or slug" class="min-w-0 border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2 text-sm text-[var(--dash-text)] outline-none focus:border-[var(--dash-primary)]">
            <label class="sr-only" for="application-status">Filter status</label>
            <select id="application-status" name="status" class="border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2 text-sm text-[var(--dash-text)] outline-none focus:border-[var(--dash-primary)]">
                <option value="">All statuses</option>
                @foreach(['draft', 'active', 'suspended', 'revoked'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ str($option)->title() }}</option>
                @endforeach
            </select>
            <button type="submit" class="border border-[var(--dash-primary)] bg-[var(--dash-primary)] px-4 py-2 text-sm font-semibold text-[var(--dash-action-text)] hover:bg-[var(--dash-primary-hover)]">Filter</button>
        </form>

        @if($applications->isEmpty())
            <div class="border border-dashed border-[var(--dash-border)] bg-[var(--dash-card)] px-6 py-14 text-center">
                <i class="bi bi-window-stack text-3xl text-[var(--dash-text-muted)]" aria-hidden="true"></i>
                <h2 class="mt-4 text-base font-semibold text-[var(--dash-text-heading)]">No applications found</h2>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Try changing the search or status filter.</p>
            </div>
        @else
            <x-table.wrapper :striped="true">
                <x-slot:head>
                    <x-table.th>Application</x-table.th>
                    <x-table.th>Organization</x-table.th>
                    <x-table.th>Protocol</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th>Updated</x-table.th>
                </x-slot:head>
                @foreach($applications as $application)
                    <tr>
                        <x-table.td>
                            <div class="font-semibold text-[var(--dash-text-heading)]">{{ $application->name }}</div>
                            <div class="text-xs text-[var(--dash-text-muted)]">{{ $application->slug }}</div>
                        </x-table.td>
                        <x-table.td>{{ $application->organization->name }}</x-table.td>
                        <x-table.td>{{ strtoupper($application->protocol_mode) }}</x-table.td>
                        <x-table.td>{{ str($application->status)->title() }}</x-table.td>
                        <x-table.td>{{ $application->updated_at?->toDateString() }}</x-table.td>
                    </tr>
                @endforeach
            </x-table.wrapper>
            <div>{{ $applications->links() }}</div>
        @endif
    </div>
</x-dashboard.layout>
