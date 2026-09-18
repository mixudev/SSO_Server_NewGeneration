<x-dashboard.layout title="Claims" breadcrumb="Claims" :user-name="auth()->user()->name" :user-email="auth()->user()->email" :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="space-y-6">
        <header><h1 class="text-2xl font-semibold text-[var(--dash-text-heading)]">Claims</h1><p class="mt-1 text-sm text-[var(--dash-text-muted)]">Manage the claim registry without exposing policy secrets.</p></header>
        @can('claims.manage')
            <form method="POST" action="{{ route('admin.claims.store') }}" class="grid gap-3 border border-[var(--dash-border)] bg-[var(--dash-card)] p-4 md:grid-cols-5">
                @csrf
                <x-form.input name="key" label="Claim key" placeholder="user.email" required /><x-form.input name="source" label="Source" placeholder="user.email" required />
                <x-form.select name="value_type" label="Value type" id="claim-value-type" required><option>string</option><option>boolean</option><option>array</option><option>json</option></x-form.select>
                <x-form.select name="sensitivity" label="Sensitivity" id="claim-sensitivity" required><option>public</option><option>personal</option><option>sensitive</option></x-form.select>
                <div class="flex items-end"><x-form.button type="submit" size="sm">Create claim</x-form.button></div>
            </form>
        @endcan
        <form method="GET" class="flex items-end gap-3"><x-form.input name="search" :value="$search" label="Search claims" placeholder="user.email" /><x-form.button type="submit" size="sm">Search</x-form.button></form>
        @if($claims->isEmpty())
            <div class="border border-dashed border-[var(--dash-border)] bg-[var(--dash-card)] px-6 py-14 text-center"><h2 class="font-semibold text-[var(--dash-text-heading)]">No claims found</h2><p class="mt-1 text-sm text-[var(--dash-text-muted)]">The registry has no matching claim.</p></div>
        @else
            <x-table.wrapper :striped="true"><x-slot:head><x-table.th>Key</x-table.th><x-table.th>Source</x-table.th><x-table.th>Type</x-table.th><x-table.th>Sensitivity</x-table.th><x-table.th>Status</x-table.th></x-slot:head>
                @foreach($claims as $claim)
                    <tr><x-table.td><strong>{{ $claim->key }}</strong><div class="text-xs text-[var(--dash-text-muted)]">{{ $claim->description }}</div></x-table.td><x-table.td>{{ $claim->source }}</x-table.td><x-table.td>{{ $claim->value_type->value }}</x-table.td><x-table.td>{{ $claim->sensitivity }}</x-table.td><x-table.td>{{ $claim->status }}</x-table.td></tr>
                @endforeach
            </x-table.wrapper>
            <div>{{ $claims->links() }}</div>
        @endif
    </div>
</x-dashboard.layout>
