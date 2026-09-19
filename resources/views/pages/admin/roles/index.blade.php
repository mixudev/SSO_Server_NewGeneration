<x-dashboard.layout title="Roles" breadcrumb="Roles" :user-name="auth()->user()->name" :user-email="auth()->user()->email" :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="space-y-6">
        <x-ui.page-header kicker="Access administration" title="Roles registry" description="Bundle approved dashboard permissions into reusable roles.">
            <x-slot:actions>
                @can('roles.manage')
                    <x-form.button type="button" size="sm" icon="plus-lg" onclick="AppModal.open('create-role-modal')">Create role</x-form.button>
                @endcan
            </x-slot:actions>
        </x-ui.page-header>

        <x-filter-section id="role-filters" title="Filter roles" description="Search role names and review effective permission counts." reset-url="{{ route('admin.roles.index') }}" search-name="search" :search-value="$search" search-placeholder="Search roles" />

        <x-table.wrapper :striped="true">
            <x-slot:head><x-table.th>Role</x-table.th><x-table.th>Permissions</x-table.th><x-table.th>Users</x-table.th><x-table.th align="right">Action</x-table.th></x-slot:head>
            @forelse ($roles as $role)
                <tr>
                    <x-table.td><strong>{{ $role->name }}</strong>@if($role->name === 'platform_admin')<div class="text-xs text-[var(--dash-primary)]">System role</div>@endif</x-table.td>
                    <x-table.td>{{ $role->permissions->count() }}</x-table.td>
                    <x-table.td>{{ $role->users_count }}</x-table.td>
                    <x-table.td align="right">@can('roles.manage')<x-form.button type="button" variant="secondary" size="sm" onclick="AppModal.open('edit-role-{{ $role->getKey() }}')" :disabled="$role->name === 'platform_admin'">Edit</x-form.button>@endcan</x-table.td>
                </tr>
            @empty
                <tr><x-table.td colspan="4"><div class="py-10 text-center text-sm text-[var(--dash-text-muted)]">No roles found.</div></x-table.td></tr>
            @endforelse
        </x-table.wrapper>
        <div>{{ $roles->links() }}</div>
    </div>

    @can('roles.manage')
        <x-app-modal id="create-role-modal" maxWidth="lg" title="Create role" description="Create a role using only registered permissions." icon="person-badge">
            <form id="create-role-form" method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5">
                @csrf
                <x-form.input name="name" label="Role name" placeholder="support_operator" required />
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <x-form.checkbox name="permissions[]" :value="$permission->name" :label="$permission->name" />
                    @endforeach
                </div>
            </form>
            <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('create-role-modal')">Cancel</x-form.button><x-form.button type="submit" form="create-role-form">Create role</x-form.button></x-slot>
        </x-app-modal>

        @foreach ($roles as $role)
            @if ($role->name !== 'platform_admin')
                <x-app-modal id="edit-role-{{ $role->getKey() }}" maxWidth="lg" title="Edit role" description="Update role membership in the permission allowlist." icon="pencil-square">
                    <form id="edit-role-form-{{ $role->getKey() }}" method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-5">
                        @csrf @method('PUT')
                        <x-form.input name="name" label="Role name" :value="$role->name" required />
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($permissions as $permission)
                                <x-form.checkbox name="permissions[]" :value="$permission->name" :label="$permission->name" :checked="$role->hasPermissionTo($permission->name)" />
                            @endforeach
                        </div>
                    </form>
                    <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('edit-role-{{ $role->getKey() }}')">Cancel</x-form.button><x-form.button type="submit" form="edit-role-form-{{ $role->getKey() }}">Save changes</x-form.button></x-slot>
                </x-app-modal>
            @endif
        @endforeach
    @endcan
</x-dashboard.layout>
