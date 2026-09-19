<x-dashboard.layout
    title="Organization details"
    breadcrumb="Organizations / Details"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <x-ui.page-header
            :back-url="route('admin.organizations.index')"
            back-label="Back to organizations"
            kicker="Identity boundary"
            :title="$organization->name"
            :description="$organization->slug"
        >
            <x-slot:actions>
                <x-status-badge :value="$organization->status" />
                @can('organizations.manage')
                    <x-form.button type="button" variant="secondary" size="sm" icon="pencil-square" onclick="AppModal.open('edit-organization-modal')">Edit organization</x-form.button>
                @endcan
            </x-slot:actions>
        </x-ui.page-header>

        <div class="bento-grid bento-grid-organization">
            <section class="bento-panel bento-panel-hero">
                <div class="bento-orbit bento-orbit-blue" aria-hidden="true"></div>
                <div class="relative z-10 flex h-full flex-col justify-between gap-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="bento-kicker">Organization overview</span>
                            <h2 class="mt-3 text-3xl font-light tracking-tight text-[var(--dash-text-heading)]">{{ $organization->name }}</h2>
                            <p class="mt-2 font-mono text-xs text-[var(--dash-text-muted)]">{{ $organization->slug }}</p>
                        </div>
                        <div class="bento-icon-mark bento-icon-mark-blue"><i class="bi bi-buildings" aria-hidden="true"></i></div>
                    </div>
                    <div class="bento-stat max-w-xs">
                        <span>Registered applications</span>
                        <strong>{{ $organization->applications_count }}</strong>
                    </div>
                </div>
            </section>

            <section class="bento-panel bento-panel-organization-detail">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="bento-kicker">Organization details</span>
                        <h2 class="bento-title">Operational profile</h2>
                    </div>
                    <i class="bi bi-info-circle text-xl text-[var(--dash-primary)]" aria-hidden="true"></i>
                </div>
                <dl class="mt-7 divide-y divide-[var(--dash-border-subtle)] text-sm">
                    <div class="flex items-center justify-between gap-4 py-3 first:pt-0"><dt class="text-[var(--dash-text-muted)]">Status</dt><dd class="font-mono text-xs font-semibold uppercase text-[var(--dash-text-heading)]">{{ $organization->status }}</dd></div>
                    <div class="flex items-center justify-between gap-4 py-3"><dt class="text-[var(--dash-text-muted)]">Applications</dt><dd class="font-mono text-xs font-semibold text-[var(--dash-text-heading)]">{{ $organization->applications_count }}</dd></div>
                    <div class="flex items-center justify-between gap-4 py-3 last:pb-0"><dt class="text-[var(--dash-text-muted)]">Created</dt><dd class="font-mono text-xs text-[var(--dash-text-heading)]">{{ $organization->created_at?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
            </section>

            <section class="bento-panel bento-panel-full">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <span class="bento-kicker">Client registry</span>
                        <h2 class="bento-title">Registered clients</h2>
                        <p class="mt-2 text-sm leading-6 text-[var(--dash-text-muted)]">Applications and credential state assigned to this organization.</p>
                    </div>
                    <a class="bento-link" href="{{ route('admin.applications.index', ['search' => $organization->slug]) }}">Manage clients <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                </div>
                <div class="mt-6 overflow-x-auto border border-[var(--dash-border-subtle)]">
                    <table class="w-full min-w-[760px] text-left">
                        <thead class="border-b border-[var(--dash-border)] bg-[var(--dash-card-hover)]">
                            <tr class="font-mono text-[10px] uppercase tracking-[.08em] text-[var(--dash-text-muted)]">
                                <th class="px-4 py-3 font-medium">Client</th>
                                <th class="px-4 py-3 font-medium">Protocol</th>
                                <th class="px-4 py-3 font-medium">Type</th>
                                <th class="px-4 py-3 font-medium">Credential</th>
                                <th class="px-4 py-3 text-right font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--dash-border-subtle)]">
                            @forelse($organization->applications as $application)
                                <tr class="text-sm transition-colors hover:bg-[var(--dash-card-hover)]">
                                    <td class="px-4 py-4"><div class="font-semibold text-[var(--dash-text-heading)]">{{ $application->name }}</div><div class="mt-1 font-mono text-[11px] text-[var(--dash-text-muted)]">{{ $application->slug }}</div></td>
                                    <td class="px-4 py-4 font-mono text-xs uppercase text-[var(--dash-text)]">{{ $application->protocol_mode }}</td>
                                    <td class="px-4 py-4 text-xs text-[var(--dash-text)]">{{ str($application->client_type)->replace('_', ' ')->title() }}</td>
                                    <td class="px-4 py-4"><span class="inline-flex items-center gap-2 font-mono text-[11px] uppercase text-[var(--dash-text-muted)]"><span class="bento-pulse-dot {{ $application->credential?->status === 'active' ? 'is-active' : '' }}"></span>{{ $application->credential?->status ?? 'not issued' }}</span></td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a class="bento-link" href="{{ route('admin.applications.show', $application) }}">View <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                                            @if($application->status === 'draft')
                                                @can('applications.delete')
                                                    <x-form.button type="button" size="xs" variant="danger" icon="trash" onclick="AppModal.open('delete-application-{{ $application->getKey() }}')">Delete</x-form.button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-[var(--dash-text-muted)]">No clients registered for this organization.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    @foreach($organization->applications->where('status', 'draft') as $application)
        @can('applications.delete')
            <x-app-modal id="delete-application-{{ $application->getKey() }}" maxWidth="md" title="Delete application" description="This action permanently removes the draft application." icon="trash" iconColor="red">
                <form id="delete-application-form-{{ $application->getKey() }}" method="POST" action="{{ route('admin.applications.destroy', $application) }}" class="grid gap-4">
                    @csrf
                    @method('DELETE')
                    <p class="text-sm text-[var(--dash-text-muted)]">Enter your current password to confirm deleting <strong class="text-[var(--dash-text-heading)]">{{ $application->name }}</strong>.</p>
                    <x-form.input name="current_password" label="Current password" type="password" required autocomplete="current-password" />
                </form>
                <x-slot name="footer"><x-form.button type="button" variant="ghost" onclick="AppModal.close('delete-application-{{ $application->getKey() }}')">Cancel</x-form.button><x-form.button type="submit" form="delete-application-form-{{ $application->getKey() }}" variant="danger">Delete</x-form.button></x-slot>
            </x-app-modal>
        @endcan
    @endforeach

    @can('organizations.manage')
        <x-app-modal id="edit-organization-modal" maxWidth="lg" title="Edit organization" description="Update the organization identity and lifecycle state." icon="pencil-square">
            <form id="organization-edit-form" method="POST" action="{{ route('admin.organizations.update', $organization) }}" class="grid gap-4">
                @csrf
                @method('PUT')
                <x-form.input name="name" label="Organization name" :value="$organization->name" required />
                <x-form.input name="slug" label="Slug identifier" :value="$organization->slug" required />
                <x-form.select name="status" label="Lifecycle status" required>
                    @foreach(['active', 'suspended', 'revoked'] as $option)
                        <option value="{{ $option }}" @selected($organization->status === $option)>{{ str($option)->title() }}</option>
                    @endforeach
                </x-form.select>
            </form>
            <x-slot name="footer">
                <x-form.button type="button" variant="ghost" onclick="AppModal.close('edit-organization-modal')">Cancel</x-form.button>
                <x-form.button type="submit" form="organization-edit-form" icon="check2">Save changes</x-form.button>
            </x-slot>
        </x-app-modal>
    @endcan
</x-dashboard.layout>
