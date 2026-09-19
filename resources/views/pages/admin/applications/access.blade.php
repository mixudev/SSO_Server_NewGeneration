<x-dashboard.layout
    :title="$application->name.' access'"
    breadcrumb="Applications / Access"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <x-ui.page-header
            :back-url="route('admin.applications.show', $application)"
            back-label="Back to application"
            :title="$application->name.' access'"
            description="Grant or revoke explicit user access. New applications are default-deny."
        />

        @can('applications.access.manage')
            <section class="bento-panel">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="bento-kicker">Access assignment</span>
                        <h2 class="bento-title">Grant a user</h2>
                    </div>
                    <i class="bi bi-person-plus text-xl text-[var(--dash-primary)]" aria-hidden="true"></i>
                </div>
                <form method="POST" action="{{ route('admin.applications.access.store', $application) }}" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    <div class="min-w-0 flex-1">
                        <label for="user_id" class="mb-2 block text-xs font-semibold text-[var(--dash-text-heading)]">Active user</label>
                        <select id="user_id" name="user_id" class="w-full border border-[var(--dash-border)] bg-[var(--dash-surface)] px-3 py-2 text-sm" required>
                            <option value="">Select a user</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->getKey() }}">{{ $user->name }} — {{ $user->email }}</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-2 text-xs text-[var(--dash-danger)]">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-form.button type="submit" size="sm" icon="person-plus">Grant access</x-form.button>
                </form>
            </section>
        @endcan

        <section class="bento-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="bento-kicker">Explicit grants</span>
                    <h2 class="bento-title">Users with access</h2>
                </div>
                <span class="font-mono text-xs text-[var(--dash-text-muted)]">{{ $accesses->total() }} total</span>
            </div>
            <div class="mt-6 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="border-b border-[var(--dash-border-subtle)] text-xs uppercase tracking-wide text-[var(--dash-text-muted)]">
                        <tr>
                            <th class="px-3 py-3">User</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Granted</th>
                            <th class="px-3 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accesses as $access)
                            <tr class="border-b border-[var(--dash-border-subtle)]">
                                <td class="px-3 py-3">
                                    <div class="font-semibold text-[var(--dash-text-heading)]">{{ $access->user->name }}</div>
                                    <div class="text-xs text-[var(--dash-text-muted)]">{{ $access->user->email }}</div>
                                </td>
                                <td class="px-3 py-3"><x-status-badge :value="$access->status" /></td>
                                <td class="px-3 py-3 text-xs text-[var(--dash-text-muted)]">{{ $access->created_at?->toIso8601String() }}</td>
                                <td class="px-3 py-3 text-right">
                                    @can('applications.access.manage')
                                        @if ($access->status === 'active')
                                            <form method="POST" action="{{ route('admin.applications.access.destroy', [$application, $access]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <x-form.button type="submit" variant="danger" size="xs" icon="person-dash">Revoke</x-form.button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-10 text-center text-sm text-[var(--dash-text-muted)]">No explicit users have access to this application.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $accesses->links() }}</div>
        </section>
    </div>
</x-dashboard.layout>
