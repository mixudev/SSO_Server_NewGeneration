<x-dashboard.layout
    title="Users & Roles"
    breadcrumb="Users & Roles"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <x-ui.page-header
            kicker="Access administration"
            title="Users & roles"
            description="Review control-plane users and their assigned roles."
        />

        <x-filter-section id="user-filters" action="{{ route('admin.users.index') }}" title="Users" description="Search control-plane users by name or email." reset-url="{{ route('admin.users.index') }}" search-name="search" :search-value="$search" search-placeholder="Search name or email...">
        </x-filter-section>

        @if ($users->isEmpty())
            <x-ui.empty-state
                icon="people"
                title="No users found"
                description="No users matched your query. Try searching with a different name or email address."
            >
                @if($search)
                    <x-form.button href="{{ route('admin.users.index') }}" variant="secondary" size="sm">
                        Clear search
                    </x-form.button>
                @endif
            </x-ui.empty-state>
        @else
            <x-table.wrapper :striped="true">
                <x-slot:head>
                    <x-table.th>User</x-table.th>
                    <x-table.th>Assigned Roles</x-table.th>
                    <x-table.th>Verification</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th align="right">Action</x-table.th>
                </x-slot:head>
                @foreach ($users as $user)
                    <tr>
                        <x-table.td>
                            <div class="font-medium text-[var(--dash-text-heading)]">{{ $user->name }}</div>
                            <div class="font-[var(--font-ppneuemontrealmono)] text-xs text-[var(--dash-text-muted)]">{{ $user->email }}</div>
                        </x-table.td>
                        <x-table.td>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($user->roles as $role)
                                    <x-ui.badge variant="primary" :pill="true">
                                        {{ $role->name }}
                                    </x-ui.badge>
                                @empty
                                    <span class="text-xs text-[var(--dash-text-muted)]">No role</span>
                                @endforelse
                            </div>
                        </x-table.td>
                        <x-table.td>
                            <x-status-badge :value="$user->email_verified_at ? 'verified' : 'pending'" />
                        </x-table.td>
                        <x-table.td>
                            <x-status-badge :value="$user->status ?? ($user->active ? 'active' : 'inactive')" />
                        </x-table.td>
                        <x-table.td align="right">
                            @if (filled($user->uuid))
                                <x-form.button href="{{ route('admin.users.show', $user) }}" variant="secondary" size="sm">
                                    View
                                </x-form.button>
                            @else
                                <span class="text-xs text-[var(--dash-text-muted)]">Identity pending</span>
                            @endif
                        </x-table.td>
                    </tr>
                @endforeach
            </x-table.wrapper>

            <x-ui.pagination :paginator="$users" />
        @endif
    </div>
</x-dashboard.layout>
