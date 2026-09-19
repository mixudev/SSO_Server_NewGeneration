<x-dashboard.layout
    title="Credential {{ ucfirst($operation) }}"
    breadcrumb="Applications / Credential"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="mx-auto max-w-2xl space-y-6">
        <x-ui.page-header
            :back-url="route('admin.applications.show', $application)"
            back-label="Back to application"
            kicker="One-time secret"
            title="Credential {{ ucfirst($operation) }}"
            :description="'Client credentials for ' . $application->name . '. Store the secret securely; it will not be shown again.'"
        />

        <x-ui.alert variant="warning" title="Important Security Notice">
            The client secret is displayed once only and cannot be retrieved later. Store it in a secure secret manager or environment configuration immediately.
        </x-ui.alert>

        <x-ui.card title="Client credentials" kicker="Generation {{ $result['generation'] }}">
            <div class="space-y-4">
                <div>
                    <label class="block font-[var(--font-ppneuemontrealmono)] text-[11px] font-semibold uppercase tracking-[0.06em] text-[var(--dash-text-muted)]">
                        Client ID
                    </label>
                    <div class="mt-1 flex items-center justify-between rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card-hover)] px-3 py-2">
                        <span id="client-id-val" class="break-all font-[var(--font-ppneuemontrealmono)] text-xs text-[var(--dash-text-heading)] select-all">{{ $result['client_id'] }}</span>
                        <x-form.button
                            type="button"
                            variant="ghost"
                            size="xs"
                            icon="copy"
                            data-copy="{{ $result['client_id'] }}"
                            aria-label="Copy Client ID"
                        >
                            Copy
                        </x-form.button>
                    </div>
                </div>

                @if($result['client_secret'])
                    <div>
                        <label class="block font-[var(--font-ppneuemontrealmono)] text-[11px] font-semibold uppercase tracking-[0.06em] text-[var(--dash-text-muted)]">
                            Client Secret
                        </label>
                        <div class="mt-1 flex items-center justify-between rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card-hover)] px-3 py-2">
                            <span id="client-secret-val" class="break-all font-[var(--font-ppneuemontrealmono)] text-xs font-semibold text-[var(--dash-primary)] select-all">{{ $result['client_secret'] }}</span>
                            <x-form.button
                                type="button"
                                variant="ghost"
                                size="xs"
                                icon="copy"
                                data-copy="{{ $result['client_secret'] }}"
                            aria-label="Copy Client Secret"
                            >
                                Copy
                            </x-form.button>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-[var(--dash-text-muted)]">
                        This public client does not require or use a client secret.
                    </p>
                @endif
            </div>

            <x-slot:footer>
                <div class="flex justify-end">
                    <x-form.button href="{{ route('admin.applications.show', $application) }}" variant="secondary" size="sm" icon="arrow-left">
                        Return to application
                    </x-form.button>
                </div>
            </x-slot:footer>
        </x-ui.card>
    </div>
</x-dashboard.layout>
