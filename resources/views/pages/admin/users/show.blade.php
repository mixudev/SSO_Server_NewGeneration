<x-dashboard.layout
    title="User details"
    breadcrumb="Users & Roles / Details"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <x-ui.page-header
            :back-url="route('admin.users.index')"
            back-label="Back to users"
            kicker="Identity account"
            :title="$user->name"
            :description="$user->email"
        >
            <x-slot:actions>
                @can('users.manage')
                    <x-form.button type="button" size="sm" icon="pencil" onclick="AppModal.open('edit-user-modal')">Edit user</x-form.button>
                    <x-form.button type="button" variant="secondary" size="sm" icon="key" onclick="AppModal.open('change-password-modal')">Change password</x-form.button>
                    <x-form.button type="button" size="sm" icon="trash" onclick="AppModal.open('delete-user-modal')">Delete user</x-form.button>
                @endcan
            </x-slot:actions>
        </x-ui.page-header>

        <div class="bento-grid">
            <section class="bento-panel bento-panel-hero lg:col-span-7">
                <div class="bento-orbit" aria-hidden="true"></div>
                <div class="relative z-10 flex h-full flex-col justify-between gap-8">
                    <div class="flex items-center gap-4">
                        <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-[var(--dash-radius)] border-2 border-[var(--dash-card)] bg-[var(--dash-primary-soft)]">
                            @if ($user->avatar_path)
                                <img src="{{ Storage::disk('public')->url($user->avatar_path) }}" alt="{{ $user->name }} avatar" class="absolute inset-0 h-full w-full object-cover object-center">
                            @else
                                <span class="grid h-full w-full place-items-center text-3xl font-semibold text-[var(--dash-primary)]">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <span class="bento-kicker">Account identity</span>
                            <h2 class="mt-2 truncate text-2xl font-semibold text-[var(--dash-text-heading)]">{{ $user->name }}</h2>
                            <p class="mt-1 truncate text-sm text-[var(--dash-text-muted)]">{{ $user->email }}</p>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-[var(--dash-primary)]">{{ $user->getRoleNames()->first() ?? 'No role assigned' }}</p>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="bento-stat"><span>Created at</span><strong>{{ $user->created_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
                        <div class="bento-stat"><span>Status</span><strong>{{ ucfirst($user->status ?? ($user->active ? 'active' : 'inactive')) }}</strong></div>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-[var(--dash-border-subtle)] pt-4">
                        <span class="bento-kicker">User UUID</span>
                        <code class="max-w-[70%] truncate text-xs text-[var(--dash-text-muted)]" title="{{ $user->uuid }}">{{ $user->uuid }}</code>
                    </div>
                    @can('users.manage')
                        @if (! $user->is(auth()->user()))
                            <div class="pt-1">
                                <x-form.button type="button" variant="{{ $user->isAccountActive() ? 'danger' : 'primary' }}" size="sm" class="w-full" onclick="AppModal.open('toggle-status-modal')">{{ $user->isAccountActive() ? 'Deactivate user' : 'Activate user' }}</x-form.button>
                            </div>
                        @endif
                    @endcan
                </div>
            </section>

            <section class="bento-panel lg:col-span-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="bento-kicker">Authorization map</span>
                        <h2 class="bento-title">Roles and effective access</h2>
                    </div>
                    <span class="text-xs text-[var(--dash-text-muted)]">{{ $user->getAllPermissions()->count() }} permissions</span>
                </div>
                <div class="mt-6">
                    <p class="bento-card-kicker">Roles</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($user->roles as $role)
                            <x-ui.badge variant="primary" :pill="true">{{ $role->name }}</x-ui.badge>
                        @empty
                            <span class="text-sm text-[var(--dash-text-muted)]">No role assigned</span>
                        @endforelse
                    </div>
                </div>
                <div class="mt-6">
                    <p class="bento-card-kicker">Permission summary</p>
                    <div class="mt-3 grid gap-2">
                        @forelse ($user->getAllPermissions()->take(3) as $permission)
                            <div class="border-l-2 border-[var(--dash-primary)] bg-[var(--dash-primary-soft)] px-3 py-2 text-xs text-[var(--dash-text-muted)]">{{ $permission->name }}</div>
                        @empty
                            <span class="text-sm text-[var(--dash-text-muted)]">No permissions assigned</span>
                        @endforelse
                    </div>
                    @if ($user->getAllPermissions()->count() > 3)
                        <p class="mt-3 text-xs font-semibold text-[var(--dash-text-muted)]">+{{ $user->getAllPermissions()->count() - 3 }} more permissions</p>
                    @endif
                </div>
            </section>
        </div>

        <section class="bento-card">
            <div>
                <p class="bento-card-kicker">Security trail</p>
                <h2 class="bento-card-title">Recent activity</h2>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Events where this user acted or was the subject. Sensitive metadata remains redacted.</p>
            </div>

            <x-table.wrapper class="mt-5" :striped="true">
                <x-slot:head>
                    <x-table.th>Event</x-table.th>
                    <x-table.th>Risk</x-table.th>
                    <x-table.th>When</x-table.th>
                </x-slot:head>

                @forelse ($activities as $activity)
                    <tr>
                        <x-table.td>
                            <span class="font-medium text-[var(--dash-text-heading)]">{{ $activity->event }}</span>
                            <div class="text-xs text-[var(--dash-text-muted)]">{{ $activity->actor === (string) $user->uuid ? 'Actor' : 'Subject' }}</div>
                        </x-table.td>
                        <x-table.td>
                            <x-ui.badge :variant="$activity->risk === 'critical' || $activity->risk === 'high' ? 'danger' : 'neutral'" :pill="true">{{ $activity->risk }}</x-ui.badge>
                        </x-table.td>
                        <x-table.td class="text-sm text-[var(--dash-text-muted)]">{{ $activity->occurred_at?->format('d M Y, H:i') }}</x-table.td>
                    </tr>
                @empty
                    <tr>
                        <x-table.td colspan="3"><span class="text-sm text-[var(--dash-text-muted)]">No security activity recorded.</span></x-table.td>
                    </tr>
                @endforelse
            </x-table.wrapper>

            <div class="mt-4 flex justify-end">
                <x-form.button href="{{ route('admin.users.activity', $user) }}" variant="ghost" size="sm" icon="clock-history">View all activity</x-form.button>
            </div>
        </section>
    </div>

    @can('users.manage')
        <x-app-modal id="edit-user-modal" maxWidth="lg" title="Edit user" description="Update identity details and the assigned role." icon="pencil">
            <form id="edit-user-form" method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">@csrf @method('PUT')<div class="grid gap-5 sm:grid-cols-2"><x-form.input name="name" label="Full name" :value="$user->name" required /><x-form.input name="email" type="email" label="Email address" :value="$user->email" required /></div><x-form.select name="role" label="Role" required>@foreach ($roles as $role)<option value="{{ $role->name }}" @selected($user->hasRole($role->name))>{{ $role->name }}</option>@endforeach</x-form.select></form>
            <x-slot name="footer"><x-form.button type="button" variant="ghost" size="sm" onclick="AppModal.close('edit-user-modal')">Cancel</x-form.button><x-form.button type="submit" form="edit-user-form" size="sm">Save changes</x-form.button></x-slot>
        </x-app-modal>
        <x-app-modal id="change-password-modal" maxWidth="md" title="Change password" description="Set a new password. The password is never displayed or logged." icon="key" iconColor="amber">
            <form id="change-password-form" method="POST" action="{{ route('admin.users.password.update', $user) }}" class="space-y-5">@csrf @method('PUT')<x-form.input name="password" type="password" label="New password" required minlength="12" /><x-form.input name="password_confirmation" type="password" label="Confirm new password" required minlength="12" /></form>
            <x-slot name="footer"><x-form.button type="button" variant="ghost" size="sm" onclick="AppModal.close('change-password-modal')">Cancel</x-form.button><x-form.button type="submit" form="change-password-form" size="sm">Change password</x-form.button></x-slot>
        </x-app-modal>
        @if (! $user->is(auth()->user()))
            <x-app-modal id="toggle-status-modal" maxWidth="md" title="{{ $user->isAccountActive() ? 'Deactivate user' : 'Activate user' }}" description="Confirm this account lifecycle change with your administrator password." icon="{{ $user->isAccountActive() ? 'shield-lock' : 'shield-check' }}" iconColor="{{ $user->isAccountActive() ? 'red' : 'emerald' }}">
                <form id="toggle-status-form" method="POST" action="{{ route('admin.users.status.update', $user) }}" class="space-y-5">@csrf @method('PUT')<input type="hidden" name="active" value="{{ $user->isAccountActive() ? 0 : 1 }}"><p class="text-sm leading-6 text-[var(--dash-text-muted)]">This will {{ $user->isAccountActive() ? 'block authentication and active access' : 'restore authentication and active access' }} for <strong class="text-[var(--dash-text-heading)]">{{ $user->email }}</strong>.</p><x-form.input name="current_password" type="password" label="Your current password" required autocomplete="current-password" /></form>
                <x-slot name="footer"><x-form.button type="button" variant="ghost" size="sm" onclick="AppModal.close('toggle-status-modal')">Cancel</x-form.button><x-form.button type="submit" form="toggle-status-form" variant="{{ $user->isAccountActive() ? 'danger' : 'primary' }}" size="sm">Confirm change</x-form.button></x-slot>
            </x-app-modal>
            <x-app-modal id="delete-user-modal" maxWidth="md" title="Delete user" description="This action permanently removes the user account and cannot be undone." icon="trash" iconColor="red">
                <form id="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" class="space-y-5">@csrf @method('DELETE')<p class="text-sm leading-6 text-[var(--dash-text-muted)]">Enter your current password to delete <strong class="text-[var(--dash-text-heading)]">{{ $user->email }}</strong>.</p><x-form.input name="current_password" type="password" label="Your current password" required autocomplete="current-password" /></form>
                <x-slot name="footer"><x-form.button type="button" variant="ghost" size="sm" onclick="AppModal.close('delete-user-modal')">Cancel</x-form.button><x-form.button type="submit" form="delete-user-form" variant="danger" size="sm">Delete user</x-form.button></x-slot>
            </x-app-modal>
        @endif
    @endcan
</x-dashboard.layout>