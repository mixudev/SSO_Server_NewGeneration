<x-dashboard.layout
    title="Organization details"
    breadcrumb="Organizations / Details"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <x-form.button href="{{ route('admin.organizations.index') }}" variant="ghost" size="sm" icon="arrow-left">Back to organizations</x-form.button>
            <p class="mt-5 text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Organization boundary</p>
            <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">{{ $organization->name }}</h1>
            <p class="mt-1 text-sm text-[var(--dash-text-muted)]">{{ $organization->slug }}</p>
        </header>

        <section class="border border-[var(--dash-border)] bg-[var(--dash-card)]">
            <header class="border-b border-[var(--dash-border)] px-6 py-5">
                <h2 class="text-base font-semibold text-[var(--dash-text-heading)]">Lifecycle and identity</h2>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Changing lifecycle status affects whether new application operations may proceed.</p>
            </header>
            <form method="POST" action="{{ route('admin.organizations.update', $organization) }}" class="space-y-5 px-6 py-6">
                @csrf
                @method('PUT')
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="name" label="Organization name" :value="$organization->name" required />
                    <x-form.input name="slug" label="Slug" :value="$organization->slug" required />
                </div>
                <div>
                    <label for="organization-status-edit" class="mb-1.5 block font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium uppercase tracking-[0.02em] text-[var(--dash-muted)]">Status <span class="text-[var(--dash-danger)]">*</span></label>
                    <select id="organization-status-edit" name="status" required class="w-full border border-[var(--dash-border)] bg-[var(--dash-card)] px-3 py-2 text-xs text-[var(--dash-text)] focus:border-[var(--dash-primary)] focus:outline-none">
                        @foreach(['active', 'suspended', 'revoked'] as $option)
                            <option value="{{ $option }}" @selected($organization->status === $option)>{{ str($option)->title() }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-xs text-[var(--dash-danger)]">{{ $message }}</p>@enderror
                </div>
                <footer class="flex justify-end border-t border-[var(--dash-border)] pt-5">
                    <x-form.button type="submit" size="sm">Save organization</x-form.button>
                </footer>
            </form>
        </section>

        <section class="grid gap-4 sm:grid-cols-2">
            <div class="border border-[var(--dash-border)] bg-[var(--dash-card)] p-6">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--dash-muted)]">Applications</p>
                <p class="mt-3 text-2xl font-semibold text-[var(--dash-text-heading)]">{{ $organization->applications_count }}</p>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Registered applications in this organization.</p>
            </div>
            <div class="border border-[var(--dash-border)] bg-[var(--dash-card)] p-6">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--dash-muted)]">Current status</p>
                <p class="mt-3 text-base font-semibold text-[var(--dash-text-heading)]">{{ str($organization->status)->title() }}</p>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Status is revalidated by application boundaries.</p>
            </div>
        </section>
    </div>
</x-dashboard.layout>
