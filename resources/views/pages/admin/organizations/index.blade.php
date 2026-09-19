<x-dashboard.layout
    title="Organizations"
    breadcrumb="Organizations"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <x-ui.page-header
            kicker="Identity boundaries"
            title="Organizations"
            description="Create organization boundaries before registering client applications."
        >
            <x-slot:actions>
                @can('organizations.manage')
                    <x-form.button type="button" size="sm" icon="plus-lg" onclick="AppModal.open('create-organization-modal')">
                        Create organization
                    </x-form.button>
                @endcan
            </x-slot:actions>
        </x-ui.page-header>

        <x-filter-section id="organization-filters" action="{{ route('admin.organizations.index') }}" title="Organizations" description="Narrow organizations by identity or lifecycle state." reset-url="{{ route('admin.organizations.index') }}" search-name="search" :search-value="$search" search-placeholder="Search name or slug...">
            <x-form.select name="status" label="Filter status" id="organization-status">
                <option value="">All statuses</option>
                @foreach(['active', 'suspended', 'revoked'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ str($option)->title() }}</option>
                @endforeach
            </x-form.select>
        </x-filter-section>

        @if ($organizations->isEmpty())
            <x-ui.empty-state
                icon="building"
                title="No organizations found"
                description="No organization records match your filter criteria."
            >
                @if($search || $status)
                    <x-form.button href="{{ route('admin.organizations.index') }}" variant="secondary" size="sm">
                        Reset filters
                    </x-form.button>
                @endif
            </x-ui.empty-state>
        @else
            <x-table.wrapper :striped="true">
                <x-slot:head>
                    <x-table.th>Organization</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th>Applications</x-table.th>
                    <x-table.th>Updated</x-table.th>
                    <x-table.th align="right">Action</x-table.th>
                </x-slot:head>
                @foreach ($organizations as $organization)
                    <tr>
                        <x-table.td>
                            <div class="font-medium text-[var(--dash-text-heading)]">{{ $organization->name }}</div>
                            <div class="font-[var(--font-ppneuemontrealmono)] text-xs text-[var(--dash-text-muted)]">{{ $organization->slug }}</div>
                        </x-table.td>
                        <x-table.td>
                            <x-status-badge :value="$organization->status" />
                        </x-table.td>
                        <x-table.td>
                            <span class="font-[var(--font-ppneuemontrealmono)] text-xs">
                                {{ $organization->applications_count }}
                            </span>
                        </x-table.td>
                        <x-table.td>
                            <span class="font-[var(--font-ppneuemontrealmono)] text-xs text-[var(--dash-text-muted)]">
                                {{ $organization->updated_at?->toDateString() }}
                            </span>
                        </x-table.td>
                        <x-table.td align="right">
                            <x-form.button href="{{ route('admin.organizations.show', $organization) }}" variant="secondary" size="sm">
                                View
                            </x-form.button>
                        </x-table.td>
                    </tr>
                @endforeach
            </x-table.wrapper>

            <x-ui.pagination :paginator="$organizations" />
        @endif
    </div>

    @can('organizations.manage')
        <x-app-modal id="create-organization-modal" maxWidth="lg" title="Create organization" description="Create an active organization before registering client applications." icon="building-add">
            <form id="create-organization-form" method="POST" action="{{ route('admin.organizations.store') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                <x-form.input name="name" label="Organization name" required />
                <x-form.input name="slug" label="Slug identifier" required />
            </form>
            <x-slot name="footer">
                <x-form.button type="button" variant="ghost" onclick="AppModal.close('create-organization-modal')">Cancel</x-form.button>
                <x-form.button type="submit" form="create-organization-form">Create organization</x-form.button>
            </x-slot>
        </x-app-modal>
    @endcan
</x-dashboard.layout>
