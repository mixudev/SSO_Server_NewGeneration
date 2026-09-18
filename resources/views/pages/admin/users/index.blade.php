<x-dashboard.layout
    title="Users & Roles"
    breadcrumb="Users & Roles"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <header>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Access administration</p>
            <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">Users &amp; roles</h1>
            <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Review control-plane users and their assigned roles.</p>
        </header>

        <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 border border-[var(--dash-border)] bg-[var(--dash-card)] p-4 md:grid-cols-[1fr_auto]">
            <x-form.input name="search" :value="$search" placeholder="Search name or email" label="Search users" />
            <div class="flex items-end">
                <x-form.button type="submit" size="sm">Search</x-form.button>
            </div>
        </form>

        @if ($users->isEmpty())
            <div class="border border-dashed border-[var(--dash-border)] bg-[var(--dash-card)] px-6 py-14 text-center">
                <i class="bi bi-people text-3xl text-[var(--dash-text-muted)]" aria-hidden="true"></i>
                <h2 class="mt-4 text-base font-semibold text-[var(--dash-text-heading)]">No users found</h2>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Try a different name or email search.</p>
            </div>
        @else
            <x-table.wrapper :striped="true">
                <x-slot:head>
                    <x-table.th>User</x-table.th>
                    <x-table.th>Roles</x-table.th>
                    <x-table.th>Verified</x-table.th>
                    <x-table.th align="right">Action</x-table.th>
                </x-slot:head>
                @foreach ($users as $user)
                    <tr>
                        <x-table.td>
                            <p class="font-semibold text-[var(--dash-text-heading)]">{{ $user->name }}</p>
                            <p class="mt-1 text-xs text-[var(--dash-text-muted)]">{{ $user->email }}</p>
                        </x-table.td>
                        <x-table.td>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($user->roles as $role)
                                    <span class="bg-[var(--dash-primary-soft)] px-2 py-1 text-[11px] font-semibold text-[var(--dash-primary)]">{{ $role->name }}</span>
                                @empty
                                    <span class="text-xs text-[var(--dash-text-muted)]">No role</span>
                                @endforelse
                            </div>
                        </x-table.td>
                        <x-table.td>{{ $user->email_verified_at ? 'Verified' : 'Pending' }}</x-table.td>
                        <x-table.td align="right"><x-form.button href="{{ route('admin.users.show', $user) }}" variant="secondary" size="sm">View</x-form.button></x-table.td>
                    </tr>
                @endforeach
            </x-table.wrapper>
            <div>{{ $users->links() }}</div>
        @endif
    </div>
</x-dashboard.layout>
