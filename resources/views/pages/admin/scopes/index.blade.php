<x-dashboard.layout title="Scopes" breadcrumb="Scopes" :user-name="auth()->user()->name" :user-email="auth()->user()->email" :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-3 md:flex-row md:items-end">
            <div><h1 class="text-2xl font-semibold text-[var(--dash-text-heading)]">Scopes</h1><p class="mt-1 text-sm text-[var(--dash-text-muted)]">Manage the scope registry used by application authorization.</p></div>
            @can('scopes.manage')<x-form.button type="button" size="sm" icon="plus-lg" onclick="AppModal.open('create-scope-modal')">Create scope</x-form.button>@endcan
        </header>
        <x-filter-section id="scope-filters" title="Filter scopes" description="Search the authorization scope registry." reset-url="{{ route('admin.scopes.index') }}" search-name="search" :search-value="$search" search-placeholder="Search scopes">
        </x-filter-section>
        @if($scopes->isEmpty())
            <div class="border border-dashed border-[var(--dash-border)] bg-[var(--dash-card)] px-6 py-14 text-center"><h2 class="font-semibold text-[var(--dash-text-heading)]">No scopes found</h2><p class="mt-1 text-sm text-[var(--dash-text-muted)]">The registry has no matching scope.</p></div>
        @else
            <x-table.wrapper :striped="true"><x-slot:head><x-table.th>Name</x-table.th><x-table.th>Risk</x-table.th><x-table.th>Status</x-table.th><x-table.th>Active applications</x-table.th><x-table.th align="right">Action</x-table.th></x-slot:head>
                @foreach($scopes as $scope)
                    <tr><x-table.td><strong>{{ $scope->name }}</strong><div class="text-xs text-[var(--dash-text-muted)]">{{ $scope->description }}</div></x-table.td><x-table.td><x-status-badge :value="$scope->risk_level" variant="{{ $scope->risk_level === 'critical' ? 'danger' : ($scope->risk_level === 'high' ? 'warning' : 'neutral') }}" /></x-table.td><x-table.td><x-status-badge :value="$scope->status" /></x-table.td><x-table.td>{{ $scope->active_applications_count }}</x-table.td><x-table.td align="right">@can('scopes.manage')<x-form.button type="button" variant="secondary" size="sm" onclick="AppModal.open('edit-scope-{{ $scope->getKey() }}')">Edit</x-form.button>@endcan</x-table.td></tr>
                @endforeach
            </x-table.wrapper>
            <div>{{ $scopes->links() }}</div>
        @endif
    </div>

    @can('scopes.manage')
        <x-app-modal id="create-scope-modal" maxWidth="lg" title="Create scope" description="Add a scope to the authorization registry." icon="shield-plus">
            <form id="create-scope-form" method="POST" action="{{ route('admin.scopes.store') }}" class="grid gap-4 md:grid-cols-2">@csrf<x-form.input name="name" label="Scope name" placeholder="account:read" required /><x-form.input name="description" label="Description" /><x-form.select name="category" label="Category" id="create-scope-category" required><option value="custom">Custom</option><option value="standard">Standard</option></x-form.select><x-form.select name="risk_level" label="Risk level" id="create-scope-risk" required><option>low</option><option>medium</option><option>high</option><option>critical</option></x-form.select></form>
            <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('create-scope-modal')">Cancel</x-form.button><x-form.button type="submit" form="create-scope-form">Create scope</x-form.button></x-slot>
        </x-app-modal>
        @foreach($scopes as $scope)
            <x-app-modal id="edit-scope-{{ $scope->getKey() }}" maxWidth="lg" title="Edit scope" description="Update registry metadata or revoke this scope." icon="pencil-square">
                <form id="edit-scope-form-{{ $scope->getKey() }}" method="POST" action="{{ route('admin.scopes.update', $scope) }}" class="grid gap-4 md:grid-cols-2">@csrf @method('PUT')<x-form.input name="name" label="Scope name" :value="$scope->name" required /><x-form.input name="description" label="Description" :value="$scope->description" /><x-form.select name="risk_level" label="Risk level" id="edit-scope-risk-{{ $scope->getKey() }}" required>@foreach(['low','medium','high','critical'] as $risk)<option value="{{ $risk }}" @selected($scope->risk_level === $risk)>{{ $risk }}</option>@endforeach</x-form.select><x-form.select name="status" label="Status" id="edit-scope-status-{{ $scope->getKey() }}" required><option value="active" @selected($scope->status === 'active')>active</option><option value="revoked" @selected($scope->status === 'revoked')>revoked</option></x-form.select></form>
                <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('edit-scope-{{ $scope->getKey() }}')">Cancel</x-form.button><x-form.button type="submit" form="edit-scope-form-{{ $scope->getKey() }}" variant="secondary">Save changes</x-form.button></x-slot>
            </x-app-modal>
        @endforeach
    @endcan
</x-dashboard.layout>