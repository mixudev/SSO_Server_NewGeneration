<x-dashboard.layout title="Security audit" breadcrumb="Security audit" :user-name="auth()->user()->name" :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')">
    <div class="space-y-6">
        <header>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--dash-primary)]">Security operations
            </p>
            <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">Audit log</h1>
            <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Read-only security events. Secrets and private material
                are redacted before display.</p>
        </header>
        <form method="GET"
            class="grid gap-3 border border-[var(--dash-border)] bg-[var(--dash-card)] p-4 md:grid-cols-4">
            <x-form.input name="event" :value="request('event')" label="Event" placeholder="APPLICATION_CREDENTIAL_ISSUED" />
            <x-form.select name="risk" label="Risk" id="audit-risk">
                <option value="">All risks</option>
                @foreach (['low', 'medium', 'high', 'critical'] as $risk)
                    <option value="{{ $risk }}" @selected(request('risk') === $risk)>{{ $risk }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="actor" :value="request('actor')" label="Actor" placeholder="User ID" />
            <div class="flex items-end"><x-form.button type="submit" size="sm">Filter audit</x-form.button></div>
        </form>
        @if ($events->isEmpty())
            <div class="border border-dashed border-[var(--dash-border)] bg-[var(--dash-card)] px-6 py-14 text-center">
                <h2 class="font-semibold text-[var(--dash-text-heading)]">No audit events found</h2>
                <p class="mt-1 text-sm text-[var(--dash-text-muted)]">Try another filter.</p>
            </div>
        @else
            <x-table.wrapper :striped="true"><x-slot:head><x-table.th>Event</x-table.th><x-table.th>Risk</x-table.th><x-table.th>Actor</x-table.th><x-table.th>Subject</x-table.th><x-table.th>Occurred</x-table.th><x-table.th>Metadata</x-table.th></x-slot:head>
                @foreach ($events as $event)
                    <tr><x-table.td><strong>{{ $event->event }}</strong></x-table.td><x-table.td>{{ $event->risk }}</x-table.td><x-table.td>{{ $event->actor ?: 'System' }}</x-table.td><x-table.td>{{ $event->subject ?: '—' }}</x-table.td><x-table.td>{{ $event->occurred_at?->toDateTimeString() }}</x-table.td><x-table.td><span
                                class="block max-w-xs break-all font-mono text-[11px]">{{ json_encode($event->safeMetadata(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</span></x-table.td>
                    </tr>
                @endforeach
            </x-table.wrapper>
            <div>{{ $events->links() }}</div>
        @endif
    </div>
</x-dashboard.layout>
