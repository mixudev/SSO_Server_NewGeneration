<x-dashboard.layout title="Claims" breadcrumb="Claims" :user-name="auth()->user()->name" :user-email="auth()->user()->email" :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-3 md:flex-row md:items-end"><div><h1 class="text-2xl font-semibold text-[var(--dash-text-heading)]">Claims</h1><p class="mt-1 text-sm text-[var(--dash-text-muted)]">Manage the claim registry without exposing policy secrets.</p></div>@can('claims.manage')<x-form.button type="button" size="sm" icon="plus-lg" onclick="AppModal.open('create-claim-modal')">Create claim</x-form.button>@endcan</header>
        <x-filter-section id="claim-filters" title="Filter claims" description="Search the registered claim definitions." reset-url="{{ route('admin.claims.index') }}" search-name="search" :search-value="$search" search-placeholder="Search claims">
        </x-filter-section>
        @if($claims->isEmpty())
            <div class="border border-dashed border-[var(--dash-border)] bg-[var(--dash-card)] px-6 py-14 text-center"><h2 class="font-semibold text-[var(--dash-text-heading)]">No claims found</h2><p class="mt-1 text-sm text-[var(--dash-text-muted)]">The registry has no matching claim.</p></div>
        @else
            <x-table.wrapper :striped="true"><x-slot:head><x-table.th>Key</x-table.th><x-table.th>Source</x-table.th><x-table.th>Type</x-table.th><x-table.th>Sensitivity</x-table.th><x-table.th>Status</x-table.th><x-table.th align="right">Action</x-table.th></x-slot:head>
                @foreach($claims as $claim)
                    <tr><x-table.td><strong>{{ $claim->key }}</strong><div class="text-xs text-[var(--dash-text-muted)]">{{ $claim->description }}</div></x-table.td><x-table.td>{{ $claim->source }}</x-table.td><x-table.td><x-status-badge :value="$claim->value_type->value" /></x-table.td><x-table.td><x-status-badge :value="$claim->sensitivity" /></x-table.td><x-table.td><x-status-badge :value="$claim->status" /></x-table.td><x-table.td align="right">@can('claims.manage')<x-form.button type="button" variant="secondary" size="sm" onclick="AppModal.open('edit-claim-{{ $claim->getKey() }}')">Edit</x-form.button>@endcan</x-table.td></tr>
                @endforeach
            </x-table.wrapper>
            <div>{{ $claims->links() }}</div>
        @endif
    </div>

    @can('claims.manage')
        <x-app-modal id="create-claim-modal" maxWidth="lg" title="Create claim" description="Add a claim definition to the registry." icon="braces">
            <form id="create-claim-form" method="POST" action="{{ route('admin.claims.store') }}" class="grid gap-4 md:grid-cols-2">@csrf<x-form.input name="key" label="Claim key" placeholder="user.email" required /><x-form.input name="description" label="Description" /><x-form.input name="source" label="Source" placeholder="user.email" required /><x-form.select name="value_type" label="Value type" id="create-claim-value-type" required><option>string</option><option>boolean</option><option>array</option><option>json</option></x-form.select><x-form.select name="sensitivity" label="Sensitivity" id="create-claim-sensitivity" required><option>public</option><option>personal</option><option>sensitive</option></x-form.select></form>
            <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('create-claim-modal')">Cancel</x-form.button><x-form.button type="submit" form="create-claim-form">Create claim</x-form.button></x-slot>
        </x-app-modal>
        @foreach($claims as $claim)
            <x-app-modal id="edit-claim-{{ $claim->getKey() }}" maxWidth="lg" title="Edit claim" description="Update or deactivate this claim definition." icon="pencil-square">
                <form id="edit-claim-form-{{ $claim->getKey() }}" method="POST" action="{{ route('admin.claims.update', $claim) }}" class="grid gap-4 md:grid-cols-2">@csrf @method('PUT')<x-form.input name="key" label="Claim key" :value="$claim->key" required /><x-form.input name="description" label="Description" :value="$claim->description" /><x-form.input name="source" label="Source" :value="$claim->source" required /><x-form.select name="value_type" label="Value type" id="edit-claim-value-type-{{ $claim->getKey() }}" required>@foreach(['string','boolean','array','json'] as $type)<option @selected($claim->value_type->value === $type)>{{ $type }}</option>@endforeach</x-form.select><x-form.select name="sensitivity" label="Sensitivity" id="edit-claim-sensitivity-{{ $claim->getKey() }}" required>@foreach(['public','personal','sensitive'] as $sensitivity)<option @selected($claim->sensitivity === $sensitivity)>{{ $sensitivity }}</option>@endforeach</x-form.select><x-form.select name="status" label="Status" id="edit-claim-status-{{ $claim->getKey() }}" required><option value="active" @selected($claim->status === 'active')>active</option><option value="revoked" @selected($claim->status === 'revoked')>revoked</option></x-form.select></form>
                <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('edit-claim-{{ $claim->getKey() }}')">Cancel</x-form.button><x-form.button type="submit" form="edit-claim-form-{{ $claim->getKey() }}" variant="secondary">Save changes</x-form.button></x-slot>
            </x-app-modal>
        @endforeach
    @endcan
</x-dashboard.layout>