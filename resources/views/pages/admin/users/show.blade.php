<x-dashboard.layout
    title="User details"
    breadcrumb="Users & Roles / Details"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <x-form.button href="{{ route('admin.users.index') }}" variant="ghost" size="sm" icon="arrow-left">Back to users</x-form.button>
            <p class="mt-5 text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">User account</p>
            <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">{{ $user->name }}</h1>
            <p class="mt-1 text-sm text-[var(--dash-text-muted)]">{{ $user->email }}</p>
        </header>

        <section class="border border-[var(--dash-border)] bg-[var(--dash-card)]">
            <header class="border-b border-[var(--dash-border)] px-6 py-5">
                <h2 class="text-base font-semibold text-[var(--dash-text-heading)]">Account access</h2>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Update identity details and assign one approved role.</p>
            </header>
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5 px-6 py-6">
                @csrf
                @method('PUT')
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="name" label="Full name" :value="$user->name" required placeholder="Enter full name" />
                    <x-form.input name="email" type="email" label="Email address" :value="$user->email" required placeholder="name@example.com" />
                </div>
                <div>
                    <label for="role" class="mb-1.5 block font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium uppercase tracking-[0.02em] text-[var(--dash-muted)]">Role <span class="text-[var(--dash-danger)]">*</span></label>
                    <select id="role" name="role" required class="w-full rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] px-3 py-2 text-xs text-[var(--dash-text)] focus:border-[var(--dash-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--dash-primary-ring)]">
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-1 text-xs text-[var(--dash-danger)]">{{ $message }}</p>@enderror
                </div>
                <footer class="flex justify-end border-t border-[var(--dash-border)] pt-5">
                    <x-form.button type="submit" size="sm">Save user</x-form.button>
                </footer>
            </form>
        </section>

        <section class="border border-[var(--dash-border)] bg-[var(--dash-card)]">
            <header class="border-b border-[var(--dash-border)] px-6 py-5">
                <h2 class="text-base font-semibold text-[var(--dash-text-heading)]">Permission summary</h2>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Effective permissions inherited from assigned roles.</p>
            </header>
            <div class="flex flex-wrap gap-2 px-6 py-6">
                @forelse ($user->getAllPermissions() as $permission)
                    <span class="border border-[var(--dash-border)] px-2.5 py-1 text-xs text-[var(--dash-text-muted)]">{{ $permission->name }}</span>
                @empty
                    <p class="text-sm text-[var(--dash-text-muted)]">No permissions assigned.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-dashboard.layout>
