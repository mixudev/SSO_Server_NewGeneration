<x-dashboard.layout
    title="Security audit"
    breadcrumb="Security audit"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    @once
        <style>
            .audit-detail-label { color: var(--dash-text-muted); font-size: 0.6875rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
            .audit-detail-value { color: var(--dash-text-heading); font-size: 0.8125rem; line-height: 1.5; margin-top: 0.375rem; overflow-wrap: anywhere; word-break: break-word; }
            .audit-detail-value-emphasis { font-weight: 700; letter-spacing: 0.01em; }
            .audit-detail-value-soft { color: var(--dash-text); font-weight: 400; }
            .audit-detail-json { white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; }
        </style>
    @endonce

    <div class="space-y-6">
        <x-ui.page-header
            kicker="Security operations"
            title="Audit log"
            description="Application security events and database mutation audits from the security defense layer."
        >
            <x-slot:actions>
                @if ($source === 'events')
                    @can('audit.export')
                        <x-form.button href="{{ route('admin.audit.export', request()->except('page')) }}" size="sm" variant="secondary" icon="download">Export CSV</x-form.button>
                    @endcan
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <div class="flex justify-center">
            <nav class="inline-flex gap-1 border border-[var(--dash-border)] bg-[var(--dash-card)] p-1 shadow-sm" aria-label="Audit sources">
                <a href="{{ route('admin.audit.index', ['source' => 'events']) }}" class="px-5 py-2.5 text-xs font-semibold transition-colors {{ $source === 'events' ? 'bg-[var(--dash-action)] text-[var(--dash-action-text)]' : 'text-[var(--dash-text-muted)] hover:bg-[var(--dash-card-hover)] hover:text-[var(--dash-text-heading)]' }}">Security events</a>
                <a href="{{ route('admin.audit.index', ['source' => 'data-audits']) }}" class="px-5 py-2.5 text-xs font-semibold transition-colors {{ $source === 'data-audits' ? 'bg-[var(--dash-action)] text-[var(--dash-action-text)]' : 'text-[var(--dash-text-muted)] hover:bg-[var(--dash-card-hover)] hover:text-[var(--dash-text-heading)]' }}">Database audits</a>
            </nav>
        </div>

        @if ($source === 'events')
            <x-filter-section id="audit-filters" action="{{ route('admin.audit.index', ['source' => 'events']) }}" title="Event filters" description="Filter authentication and security events." reset-url="{{ route('admin.audit.index', ['source' => 'events']) }}" search-name="event" :search-value="request('event')" search-placeholder="Search event type...">
                <x-form.select name="risk" label="Risk level" id="audit-risk">
                    <option value="">All risks</option>
                    @foreach (['low', 'medium', 'high', 'critical'] as $risk)
                        <option value="{{ $risk }}" @selected(request('risk') === $risk)>{{ ucfirst($risk) }}</option>
                    @endforeach
                </x-form.select>
                <x-form.input name="actor" :value="request('actor')" label="Actor" placeholder="User ID or email" />
            </x-filter-section>
        @else
            <x-filter-section id="data-audit-filters" action="{{ route('admin.audit.index', ['source' => 'data-audits']) }}" title="Database audit filters" description="Inspect model mutations captured by mixudev/security-defense." reset-url="{{ route('admin.audit.index', ['source' => 'data-audits']) }}" search-name="search" :search-value="request('search')" search-placeholder="Search actor, model, IP, or URL...">
                <x-form.input name="auditable_type" :value="request('auditable_type')" label="Model type" />
                <x-form.select name="tampered" label="Integrity status">
                    <option value="">All records</option>
                    <option value="1" @selected(request('tampered') === '1')>Tampered</option>
                    <option value="0" @selected(request('tampered') === '0')>Clean</option>
                </x-form.select>
            </x-filter-section>
        @endif

        @if ($source === 'events')
            @if ($events->isEmpty())
                <x-ui.empty-state icon="shield-slash" title="No audit events found" description="No recorded security events match the current filters." />
            @else
                <x-table.wrapper :striped="true">
                    <x-slot:head>
                        <x-table.th>Event</x-table.th>
                        <x-table.th>Risk</x-table.th>
                        <x-table.th>Action</x-table.th>
                    </x-slot:head>
                    @foreach ($events as $event)
                        @php
                            $riskVariant = match ($event->risk) {
                                'critical' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                default => 'neutral',
                            };
                            $eventModal = 'audit-event-' . $event->getKey();
                        @endphp
                        <tr>
                            <x-table.td><span class="font-medium text-[var(--dash-text-heading)]">{{ $event->event }}</span></x-table.td>
                            <x-table.td><x-ui.badge :variant="$riskVariant" :pill="true">{{ $event->risk }}</x-ui.badge></x-table.td>
                            <x-table.td><x-form.button type="button" size="xs" variant="secondary" icon="eye" onclick="AppModal.open('{{ $eventModal }}')">View</x-form.button></x-table.td>
                        </tr>
                        <x-app-modal :id="$eventModal" maxWidth="lg" title="Security event" description="Read-only event details. Sensitive metadata is redacted." icon="shield-check">
                            <dl class="grid min-w-0 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <dt class="audit-detail-label">Event</dt>
                                    <dd class="audit-detail-value audit-detail-value-emphasis">{{ $event->event }}</dd>
                                </div>
                                <div>
                                    <dt class="audit-detail-label">Risk</dt>
                                    <dd class="audit-detail-value audit-detail-value-soft uppercase">{{ $event->risk }}</dd>
                                </div>
                                <div>
                                    <dt class="audit-detail-label">Subject</dt>
                                    <dd class="audit-detail-value audit-detail-value-soft">{{ $event->subject ?: '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="audit-detail-label">Actor / User</dt>
                                    <dd class="audit-detail-value audit-detail-value-soft">{{ $actorNames[(string) $event->actor] ?? ($event->actor ? 'User ID · ' . $event->actor : 'System') }}</dd>
                                </div>
                                <div>
                                    <dt class="audit-detail-label">Occurred</dt>
                                    <dd class="audit-detail-value audit-detail-value-soft">{{ $event->occurred_at?->toDateTimeString() }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="audit-detail-label">Request ID</dt>
                                    <dd class="audit-detail-value audit-detail-value-soft break-all">{{ $event->request_id ?: '—' }}</dd>
                                </div>
                            </dl>
                            <div class="mt-5"><p class="audit-detail-label">Safe metadata</p><pre class="audit-detail-json mt-2 max-h-64 overflow-auto border border-[var(--dash-border)] bg-[var(--dash-card-hover)] p-3 font-mono text-[11px] leading-5 text-[var(--dash-text)]">{{ json_encode($event->safeMetadata(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre></div>
                            <x-slot name="footer"><x-form.button type="button" variant="ghost" size="sm" onclick="AppModal.close('{{ $eventModal }}')">Close</x-form.button></x-slot>
                        </x-app-modal>
                    @endforeach
                </x-table.wrapper>
                <x-ui.pagination :paginator="$events" />
            @endif
        @else
            @if ($dataAudits->isEmpty())
                <x-ui.empty-state icon="database-x" title="No database audits found" description="No database mutation audit matches the current filters." />
            @else
                <x-table.wrapper :striped="true">
                    <x-slot:head>
                        <x-table.th>Mutation</x-table.th>
                        <x-table.th>Integrity</x-table.th>
                        <x-table.th>Action</x-table.th>
                    </x-slot:head>
                    @foreach ($dataAudits as $audit)
                        @php($auditModal = 'database-audit-' . $audit->getKey())
                        <tr>
                            <x-table.td><span class="font-medium text-[var(--dash-text-heading)]">{{ $audit->event }} · {{ class_basename($audit->auditable_type) }}</span></x-table.td>
                            <x-table.td><x-ui.badge :variant="$audit->is_tampered ? 'danger' : 'success'" :pill="true">{{ $audit->is_tampered ? 'Tampered' : 'Clean' }}</x-ui.badge></x-table.td>
                            <x-table.td><x-form.button type="button" size="xs" variant="secondary" icon="eye" onclick="AppModal.open('{{ $auditModal }}')">View</x-form.button></x-table.td>
                        </tr>
                        <x-app-modal :id="$auditModal" maxWidth="lg" title="Database audit" description="Read-only mutation and integrity details." icon="database-check">
                            <dl class="grid min-w-0 gap-4 sm:grid-cols-2">
                                <div><dt class="audit-detail-label">Mutation</dt><dd class="audit-detail-value audit-detail-value-soft">{{ $audit->event }}</dd></div>
                                <div><dt class="audit-detail-label">Integrity</dt><dd class="audit-detail-value audit-detail-value-soft">{{ $audit->is_tampered ? 'Tampered' : 'Clean' }}</dd></div>
                                <div><dt class="audit-detail-label">Model</dt><dd class="audit-detail-value break-all">{{ $audit->auditable_type }}</dd></div>
                                <div><dt class="audit-detail-label">Record ID</dt><dd class="audit-detail-value break-all">{{ $audit->auditable_id }}</dd></div>
                                <div><dt class="audit-detail-label">Actor</dt><dd class="audit-detail-value audit-detail-value-soft">{{ $audit->actor_id ?: 'System' }}</dd></div>
                                <div><dt class="audit-detail-label">Occurred</dt><dd class="audit-detail-value audit-detail-value-soft">{{ $audit->created_at?->toDateTimeString() }}</dd></div>
                            </dl>
                            <div class="mt-5 grid gap-4 sm:grid-cols-2"><div><p class="audit-detail-label">Request</p><p class="mt-2 break-all font-mono text-xs text-[var(--dash-text)]">{{ $audit->request_method }} {{ $audit->request_url ?: '—' }}</p></div><div><p class="audit-detail-label">Modified fields</p><p class="mt-2 break-all font-mono text-xs text-[var(--dash-text)]">{{ implode(', ', $audit->modified_fields ?? []) ?: '—' }}</p></div></div>
                            <x-slot name="footer"><x-form.button type="button" variant="ghost" size="sm" onclick="AppModal.close('{{ $auditModal }}')">Close</x-form.button></x-slot>
                        </x-app-modal>
                    @endforeach
                </x-table.wrapper>
                <x-ui.pagination :paginator="$dataAudits" />
            @endif
        @endif
    </div>
</x-dashboard.layout>