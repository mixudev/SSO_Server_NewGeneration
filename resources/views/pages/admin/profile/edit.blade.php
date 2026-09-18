<x-dashboard.layout
    title="Edit Profile"
    breadcrumb="Account / Edit profile"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <x-form.button href="{{ route('admin.profile.show') }}" variant="ghost" size="sm" icon="arrow-left">Back to profile</x-form.button>
            <h1 class="mt-3 text-2xl font-semibold text-[var(--dash-text-heading)]">Edit profile</h1>
            <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Update the identity shown in the admin control plane.</p>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] p-6">
            @csrf
            @method('PUT')
            <div class="grid gap-5">
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-[var(--dash-text-heading)]">Full name</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="120" autocomplete="name" class="w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2.5 text-sm text-[var(--dash-text)] outline-none focus:border-[var(--dash-primary)]">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-[var(--dash-text-heading)]">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" class="w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2.5 text-sm text-[var(--dash-text)] outline-none focus:border-[var(--dash-primary)]">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-6 flex flex-col-reverse justify-end gap-3 border-t border-[var(--dash-border)] pt-6 sm:flex-row">
                <x-form.button href="{{ route('admin.profile.show') }}" variant="secondary" size="sm">Cancel</x-form.button>
                <x-form.button type="submit" size="sm" icon="check2">Save changes</x-form.button>
            </div>
        </form>
    </div>
</x-dashboard.layout>
