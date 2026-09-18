<x-dashboard.layout title="Scopes" breadcrumb="Scopes" :user-name="auth()->user()->name" :user-email="auth()->user()->email" :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="space-y-6">
        <header><h1 class="text-2xl font-semibold text-[var(--dash-text-heading)]">Scopes</h1><p class="mt-1 text-sm text-[var(--dash-text-muted)]">Manage the scope registry used by application authorization.</p></header>
        @can('scopes.manage')
            <form method="POST" action="{{ route('admin.scopes.store') }}" class="grid gap-3 border border-[var(--dash-border)] bg-[var(--dash-card)] p-4 md:grid-cols-5">
                @csrf
                <x-form.input name="name" label="Scope name" placeholder="account:read" required />
                <x-form.input name="description" label="Description" />
                <x-form.select name="category" label="Category" id="scope-category" required><option value="custom">Custom</option><option value="standard">Standard</option></x-form.select>
                <x-form.select name="risk_level" label="Risk level" id="scope-risk" required><option>low</option><option>medium</option><option>high</option><option>critical</option></x-form.select>
                <div class="flex items-end"><x-form.button type="submit" size="sm">Create scope</x-form.button></div>
            </form>
        @endcan
        <form method="GET" class="flex items-end gap-3"><x-form.input name="search" :value="$search" label="Search scopes" placeholder="account:read" /><x-form.button type="submit" size="sm">Search</x-form.button></form>
        @if($scopes->isEmpty())
            <div class="border border-dashed border-[var(--dash-border)] bg-[var(--dash-card)] px-6 py-14 text-center"><h2 class="font-semibold text-[var(--dash-text-heading)]">No scopes found</h2><p class="mt-1 text-sm text-[var(--dash-text-muted)]">The registry has no matching scope.</p></div>
        @else
            <x-table.wrapper :striped="true"><x-slot:head><x-table.th>Name</x-table.th><x-table.th>Risk</x-table.th><x-table.th>Status</x-table.th><x-table.th>Active applications</x-table.th></x-slot:head>
                @foreach($scopes as $scope)
                    <tr><x-table.td><strong>{{ $scope->name }}</strong><div class="text-xs text-[var(--dash-text-muted)]">{{ $scope->description }}</div></x-table.td><x-table.td><x-status-badge :value="$scope->risk_level" variant="{{ $scope->risk_level === 'critical' ? 'danger' : ($scope->risk_level === 'high' ? 'warning' : 'neutral') }}" /></x-table.td><x-table.td><x-status-badge :value="$scope->status" /></x-table.td><x-table.td>{{ $scope->active_applications_count }}</x-table.td></tr>
                    @can('scopes.manage')
                        <tr><x-table.td colspan="4"><form method="POST" action="{{ route('admin.scopes.update', $scope) }}" class="flex flex-wrap items-end gap-2">@csrf @method('PUT')<input type="hidden" name="name" value="{{ $scope->name }}"><input type="hidden" name="description" value="{{ $scope->description }}"><label class="text-[11px] text-[var(--dash-muted)]">Risk<select name="risk_level" class="ml-1 border border-[var(--dash-border)] bg-[var(--dash-card)] px-2 py-1 text-xs">@foreach(['low','medium','high','critical'] as $risk)<option @selected($scope->risk_level === $risk)>{{ $risk }}</option>@endforeach</select></label><label class="text-[11px] text-[var(--dash-muted)]">Status<select name="status" class="ml-1 border border-[var(--dash-border)] bg-[var(--dash-card)] px-2 py-1 text-xs"><option value="active" @selected($scope->status === 'active')>active</option><option value="revoked" @selected($scope->status === 'revoked')>revoked</option></select></label><x-form.button type="submit" size="sm" variant="secondary">Update</x-form.button></form></x-table.td></tr>
                    @endcan
                @endforeach
            </x-table.wrapper>
            <div>{{ $scopes->links() }}</div>
        @endif
    </div>
</x-dashboard.layout>
