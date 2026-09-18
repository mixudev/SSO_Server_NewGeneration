<x-dashboard.layout title="Scopes" breadcrumb="Scopes" :user-name="auth()->user()->name" :user-email="auth()->user()->email" :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="space-y-6">
        <header>
            <h1 class="text-2xl font-semibold text-[var(--dash-text-heading)]">Scopes</h1>
            <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Manage the scope registry used by application authorization.</p>
        </header>
        <form method="POST" action="{{ route('admin.scopes.store') }}" class="grid gap-3 border border-[var(--dash-border)] bg-[var(--dash-card)] p-4 md:grid-cols-4">
            @csrf
            <x-form.input name="name" label="Scope name" placeholder="account:read" required />
            <x-form.input name="description" label="Description" />
            <select name="category" class="border border-[var(--dash-border)] bg-[var(--dash-card)] px-3 py-2 text-xs"><option value="custom">Custom</option><option value="standard">Standard</option></select>
            <div class="flex items-end"><x-form.button type="submit" size="sm">Create scope</x-form.button></div>
        </form>
        <form method="GET" class="flex items-end gap-3"><x-form.input name="search" :value="$search" label="Search scopes" placeholder="account:read" /><x-form.button type="submit" size="sm">Search</x-form.button></form>
        <x-table.wrapper :striped="true">
            <x-slot:head><x-table.th>Name</x-table.th><x-table.th>Risk</x-table.th><x-table.th>Status</x-table.th><x-table.th>Active applications</x-table.th></x-slot:head>
            @foreach($scopes as $scope)
                <tr><x-table.td><strong>{{ $scope->name }}</strong><div class="text-xs text-[var(--dash-text-muted)]">{{ $scope->description }}</div></x-table.td><x-table.td>{{ $scope->risk_level }}</x-table.td><x-table.td>{{ $scope->status }}</x-table.td><x-table.td>{{ $scope->active_applications_count }}</x-table.td></tr>
            @endforeach
        </x-table.wrapper>
        <div>{{ $scopes->links() }}</div>
    </div>
</x-dashboard.layout>
