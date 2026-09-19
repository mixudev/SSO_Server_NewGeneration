<x-dashboard.layout
    title="Applications"
    breadcrumb="Applications"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <x-ui.page-header
            kicker="Identity resources"
            title="Applications"
            description="Manage registered client applications and their lifecycle state."
        >
            <x-slot:actions>
                @can('applications.create')
                    <x-form.button href="{{ route('admin.applications.wizard.basic') }}" size="sm" icon="plus-lg">
                        Create application
                    </x-form.button>
                @endcan
            </x-slot:actions>
        </x-ui.page-header>

        <x-filter-section id="application-filters" action="{{ route('admin.applications.index') }}" title="Applications" description="Narrow applications by identity or lifecycle state." reset-url="{{ route('admin.applications.index') }}" search-name="search" :search-value="$search" search-placeholder="Search name or slug...">
            <x-form.select id="application-status" name="status" label="Filter status">
                <option value="">All statuses</option>
                @foreach(['draft', 'active', 'suspended', 'revoked'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ str($option)->title() }}</option>
                @endforeach
            </x-form.select>
        </x-filter-section>

        @if($applications->isEmpty())
            <x-ui.empty-state
                icon="window-stack"
                title="No applications found"
                description="No client applications match your search criteria. Try adjusting your query or status filter."
            >
                @if($search || $status)
                    <x-form.button href="{{ route('admin.applications.index') }}" variant="secondary" size="sm">
                        Reset filters
                    </x-form.button>
                @elseif(auth()->user()->can('applications.create'))
                    <x-form.button href="{{ route('admin.applications.wizard.basic') }}" size="sm" icon="plus-lg">
                        Create first application
                    </x-form.button>
                @endif
            </x-ui.empty-state>
        @else
            <x-table.wrapper :striped="true">
                <x-slot:head>
                    <x-table.th>Application</x-table.th>
                    <x-table.th>Organization</x-table.th>
                    <x-table.th>Protocol</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th>Updated</x-table.th>
                    <x-table.th align="right">Action</x-table.th>
                </x-slot:head>
                @foreach($applications as $application)
                    <tr>
                        <x-table.td>
                            <div class="font-medium text-[var(--dash-text-heading)]">{{ $application->name }}</div>
                            <div class="font-[var(--font-ppneuemontrealmono)] text-xs text-[var(--dash-text-muted)]">{{ $application->slug }}</div>
                        </x-table.td>
                        <x-table.td>{{ $application->organization->name }}</x-table.td>
                        <x-table.td>
                            <x-ui.badge variant="secondary">
                                {{ strtoupper($application->protocol_mode) }}
                            </x-ui.badge>
                        </x-table.td>
                        <x-table.td>
                            <x-status-badge :value="$application->status" />
                        </x-table.td>
                        <x-table.td>
                            <span class="font-[var(--font-ppneuemontrealmono)] text-xs text-[var(--dash-text-muted)]">
                                {{ $application->updated_at?->toDateString() }}
                            </span>
                        </x-table.td>
                        <x-table.td align="right">
                            <x-form.button href="{{ route('admin.applications.show', $application) }}" variant="secondary" size="sm">
                                View
                            </x-form.button>
                        </x-table.td>
                    </tr>
                @endforeach
            </x-table.wrapper>

            <x-ui.pagination :paginator="$applications" />
        @endif
    </div>
</x-dashboard.layout>
