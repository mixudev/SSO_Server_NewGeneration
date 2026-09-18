<x-dashboard.layout
    title="Organizations"
    breadcrumb="Organizations"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <header>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Identity boundaries</p>
            <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">Organizations</h1>
            <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Review organization lifecycle state and application ownership boundaries.</p>
        </header>

        <form method="GET" action="{{ route('admin.organizations.index') }}" class="grid gap-3 border border-[var(--dash-border)] bg-[var(--dash-card)] p-4 md:grid-cols-[1fr_180px_auto]">
            <x-form.input name="search" :value="$search" label="Search organizations" placeholder="Search name or slug" />
            <x-form.select name="status" label="Status" id="organization-status">
                <option value="">All statuses</option>
                @foreach(['active', 'suspended', 'revoked'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ str($option)->title() }}</option>
                @endforeach
            </x-form.select>
            <div class="flex items-end"><x-form.button type="submit" size="sm">Filter</x-form.button></div>
        </form>

        @if ($organizations->isEmpty())
            <div class="border border-dashed border-[var(--dash-border)] bg-[var(--dash-card)] px-6 py-14 text-center">
                <i class="bi bi-building text-3xl text-[var(--dash-text-muted)]" aria-hidden="true"></i>
                <h2 class="mt-4 text-base font-semibold text-[var(--dash-text-heading)]">No organizations found</h2>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Try another name, slug, or lifecycle status.</p>
            </div>
        @else
            <x-table.wrapper :striped="true">
                <x-slot:head>
                    <x-table.th>Organization</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th>Applications</x-table.th>
                    <x-table.th>Updated</x-table.th>
                    <x-table.th align="right">Action</x-table.th>
                </x-slot:head>
                @foreach ($organizations as $organization)
                    <tr>
                        <x-table.td>
                            <p class="font-semibold text-[var(--dash-text-heading)]">{{ $organization->name }}</p>
                            <p class="mt-1 text-xs text-[var(--dash-text-muted)]">{{ $organization->slug }}</p>
                        </x-table.td>
                        <x-table.td>{{ str($organization->status)->title() }}</x-table.td>
                        <x-table.td>{{ $organization->applications_count }}</x-table.td>
                        <x-table.td>{{ $organization->updated_at?->toDateString() }}</x-table.td>
                        <x-table.td align="right"><x-form.button href="{{ route('admin.organizations.show', $organization) }}" variant="secondary" size="sm">View</x-form.button></x-table.td>
                    </tr>
                @endforeach
            </x-table.wrapper>
            <div>{{ $organizations->links() }}</div>
        @endif
    </div>
</x-dashboard.layout>
