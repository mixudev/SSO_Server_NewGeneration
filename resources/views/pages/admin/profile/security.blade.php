<x-dashboard.layout
    title="Security Center"
    breadcrumb="Account / Security"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">
        <header class="flex items-start gap-4">
            <a href="{{ route('admin.profile.show') }}" class="mt-1 inline-flex h-9 w-9 items-center justify-center border border-[var(--dash-border)] text-[var(--dash-text-muted)] transition hover:border-[var(--dash-primary)] hover:text-[var(--dash-primary)]" aria-label="Back to profile"><i class="bi bi-arrow-left" aria-hidden="true"></i></a>
            <div><p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Account protection</p><h1 class="mt-2 text-2xl font-semibold leading-tight text-[var(--dash-text-heading)]">Security center</h1><p class="mt-2 max-w-2xl text-[13px] leading-5 text-[var(--dash-text-muted)]">Manage authentication methods and active devices from one focused workspace.</p></div>
        </header>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="flex flex-col border border-[var(--dash-border)] bg-[var(--dash-card)]">
                <header class="flex items-start justify-between gap-4 border-b border-[var(--dash-border)] px-6 py-5"><div><p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Protection</p><h2 class="mt-1 text-lg font-semibold text-[var(--dash-text-heading)]">Two-factor authentication</h2><p class="mt-2 text-[13px] leading-5 text-[var(--dash-text-muted)]">Require a one-time code after password login.</p></div><span class="whitespace-nowrap px-2.5 py-1 text-xs font-semibold {{ $security['two_factor_enabled'] ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $security['two_factor_enabled'] ? 'Enabled' : 'Not enabled' }}</span></header>
                <div class="flex-1 px-6 py-6"><p class="text-sm leading-6 text-[var(--dash-text-muted)]">Use an authenticator app to add a second layer of protection to this account.</p></div>
                <footer class="border-t border-[var(--dash-border)] px-6 py-4">
                    @if ($security['two_factor_enabled'])
                        <form method="POST" action="{{ route('two-factor.disable') }}" class="flex flex-wrap items-end gap-3" data-confirm="2FA akan dinonaktifkan untuk akun ini." data-confirm-type="warning" data-confirm-title="Nonaktifkan 2FA" data-confirm-btn="Nonaktifkan">@csrf @method('DELETE')<div><label for="disable-two-factor-password" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-[var(--dash-text-muted)]">Confirm password</label><input id="disable-two-factor-password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2 text-sm outline-none focus:border-[var(--dash-primary)]"></div><button class="border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit">Disable 2FA</button></form>@else
                        <a href="{{ route('admin.profile.security.two-factor') }}" class="inline-flex bg-[var(--dash-primary)] px-3 py-2 text-sm font-semibold text-white hover:opacity-90" data-confirm="Setup 2FA akan dimulai untuk akun ini." data-confirm-title="Setup 2FA" data-confirm-btn="Continue">Set up 2FA</a>
                    @endif
                </footer>
            </section>

            <section class="flex flex-col border border-[var(--dash-border)] bg-[var(--dash-card)]">
                <header class="flex items-start justify-between gap-4 border-b border-[var(--dash-border)] px-6 py-5"><div><p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Passwordless access</p><h2 class="mt-1 text-lg font-semibold text-[var(--dash-text-heading)]">Passkeys</h2><p class="mt-2 text-[13px] leading-5 text-[var(--dash-text-muted)]">Use biometrics or a device PIN for sign-in.</p></div><i class="bi bi-fingerprint text-2xl text-[var(--dash-primary)]" aria-hidden="true"></i></header>
                <div class="flex-1 space-y-3 px-6 py-6">@forelse ($security['passkeys'] as $passkey)<div class="flex items-center justify-between gap-3 border border-[var(--dash-border)] p-3"><div class="min-w-0"><p class="truncate text-sm font-semibold text-[var(--dash-text-heading)]">{{ $passkey['name'] }}</p><p class="mt-1 text-xs text-[var(--dash-text-muted)]">Registered {{ $passkey['created_at'] ?? 'date unavailable' }}</p></div><form method="POST" action="{{ route('admin.profile.security.passkeys.destroy', $passkey['id']) }}" class="sensitive-passkey-remove" data-passkey-name="{{ $passkey['name'] }}"><div>@csrf @method('DELETE')<button class="text-xs font-semibold text-red-700 hover:underline" type="submit">Remove</button></div></form></div>@empty<p class="border border-dashed border-[var(--dash-border)] p-3 text-sm text-[var(--dash-text-muted)]">No passkeys registered.</p>@endforelse</div>
                <footer class="border-t border-[var(--dash-border)] px-6 py-4"><button type="button" id="register-passkey" class="inline-flex bg-[var(--dash-primary)] px-3 py-2 text-sm font-semibold text-white hover:opacity-90" onclick="AppModal.open('passkey-name-modal')">Add passkey</button></footer>
            </section>
        </div>

        <section class="flex flex-col border border-[var(--dash-border)] bg-[var(--dash-card)]">
            <header class="flex items-start justify-between gap-4 border-b border-[var(--dash-border)] px-6 py-5"><div><p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Device access</p><h2 class="mt-1 text-lg font-semibold text-[var(--dash-text-heading)]">Active sessions</h2><p class="mt-2 text-[13px] leading-5 text-[var(--dash-text-muted)]">Review where this account is signed in and revoke access you do not recognize.</p></div><span class="bg-[var(--dash-primary-soft)] px-2.5 py-1 text-xs font-semibold text-[var(--dash-primary)]">{{ count($security['sessions']) }} device(s)</span></header>
            <div class="space-y-3 px-6 py-6">@forelse ($security['sessions'] as $session)<div class="flex flex-col justify-between gap-3 border border-[var(--dash-border)] p-4 md:flex-row md:items-center"><div><p class="text-sm font-semibold text-[var(--dash-text-heading)]">{{ $session['device_name'] ?? 'Unknown device' }} @if ($session['is_current_device'] ?? false)<span class="ml-2 bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">Current</span>@endif</p><p class="mt-1 text-xs leading-5 text-[var(--dash-text-muted)]">{{ $session['platform'] ?? 'Platform unavailable' }} · {{ $session['ip_address'] ?? 'IP unavailable' }} · {{ isset($session['last_activity']) ? \Carbon\Carbon::parse($session['last_activity'])->diffForHumans() : 'Activity unavailable' }}</p></div>@if (!($session['is_current_device'] ?? false))<form method="POST" action="{{ route('admin.profile.security.sessions.destroy', $session['id']) }}" data-confirm="Sesi perangkat ini akan dicabut." data-confirm-type="delete" data-confirm-title="Revoke session" data-confirm-btn="Revoke"><div>@csrf @method('DELETE')<button type="submit" class="border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Revoke session</button></div></form>@endif</div>@empty<p class="border border-dashed border-[var(--dash-border)] p-4 text-sm text-[var(--dash-text-muted)]">No active session data is available.</p>@endforelse</div>
            @if (count($security['sessions']) > 1)<footer class="border-t border-[var(--dash-border)] px-6 py-4"><form method="POST" action="{{ route('admin.profile.security.sessions.destroy-others') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end" data-confirm="Semua sesi selain perangkat ini akan dicabut." data-confirm-type="delete" data-confirm-title="Revoke other sessions" data-confirm-btn="Revoke sessions"><div><label for="revoke-password" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-[var(--dash-text-muted)]">Confirm password to revoke other sessions</label><input id="revoke-password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="border border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2 text-sm outline-none focus:border-[var(--dash-primary)]"></div><button type="submit" class="border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Revoke other sessions</button></form></footer>@endif
        </section>
    </div>

    <x-app-modal id="passkey-name-modal" maxWidth="md" title="Add a passkey" description="Give this device a name so you can recognize it later." icon="fingerprint" iconColor="indigo">
        <div>
            <label for="passkey-name">Device name</label>
            <input id="passkey-name" type="text" maxlength="80" value="This device" autocomplete="off" placeholder="e.g. Work laptop">
            <p class="mt-2 text-xs text-[var(--dash-text-muted)]">The name is only used to identify this passkey in your security center.</p>
        </div>
        <x-slot name="footer">
            <button type="button" onclick="AppModal.close('passkey-name-modal')" class="modal-btn-cancel">Cancel</button>
            <button type="button" id="confirm-passkey-name" class="modal-btn-primary">Continue</button>
        </x-slot>
    </x-app-modal>

    <x-app-modal id="passkey-remove-modal" maxWidth="md" title="Remove passkey" description="Confirm your password before removing this sensitive sign-in method." icon="shield-lock" iconColor="red">
        <form id="passkey-remove-form" method="POST">
            @csrf
            @method('DELETE')
            <p id="passkey-remove-name" class="mb-4 text-sm font-semibold text-[var(--dash-text-heading)]"></p>
            <label for="passkey-remove-password">Current password</label>
            <div class="relative">
                <input id="passkey-remove-password" name="current_password" type="password" required autocomplete="current-password" placeholder="Enter your current password" class="pr-10">
                <button type="button" class="password-toggle absolute inset-y-0 right-0 px-3 text-[var(--dash-text-muted)]" data-target="passkey-remove-password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" onclick="AppModal.close('passkey-remove-modal')" class="modal-btn-cancel">Cancel</button>
            <button type="button" id="confirm-passkey-remove" class="modal-btn-danger">Remove passkey</button>
        </x-slot>
    </x-app-modal>

    <script>
        (() => {
            document.querySelectorAll('.sensitive-passkey-remove').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    document.getElementById('passkey-remove-form').action = form.action;
                    document.getElementById('passkey-remove-name').textContent = `Device: ${form.dataset.passkeyName}`;
                    document.getElementById('passkey-remove-password').value = '';
                    AppModal.open('passkey-remove-modal');
                });
            });

            document.getElementById('confirm-passkey-remove')?.addEventListener('click', () => {
                const form = document.getElementById('passkey-remove-form');
                if (!form.reportValidity()) return;
                confirmAction({
                    type: 'delete',
                    title: 'Remove this passkey?',
                    description: 'This device will no longer be able to sign in with this passkey.',
                    confirmText: 'Remove passkey',
                    onConfirm: () => form.submit(),
                });
            });

            document.querySelectorAll('.password-toggle').forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    const input = document.getElementById(toggle.dataset.target);
                    const icon = toggle.querySelector('i');
                    const visible = input.type === 'text';
                    input.type = visible ? 'password' : 'text';
                    icon.classList.toggle('bi-eye', visible);
                    icon.classList.toggle('bi-eye-slash', !visible);
                });
            });
        })();

        (() => {
            const button = document.getElementById('register-passkey');
            const confirmButton = document.getElementById('confirm-passkey-name');
            const nameInput = document.getElementById('passkey-name');
            if (!button || !confirmButton || !nameInput) return;

            const toBuffer = (value) => {
                const padding = '='.repeat((4 - value.length % 4) % 4);
                const raw = atob((value + padding).replaceAll('-', '+').replaceAll('_', '/'));
                return Uint8Array.from(raw, (character) => character.charCodeAt(0)).buffer;
            };
            const toBase64Url = (buffer) => {
                const bytes = new Uint8Array(buffer);
                let binary = '';
                bytes.forEach((byte) => { binary += String.fromCharCode(byte); });
                return btoa(binary).replaceAll('+', '-').replaceAll('/', '_').replaceAll('=', '');
            };

            confirmButton.addEventListener('click', async () => {
                const name = nameInput.value.trim();
                if (!name) {
                    nameInput.focus();
                    return;
                }
                AppModal.close('passkey-name-modal');

                if (!window.PublicKeyCredential) {
                    window.AppPopup?.warning({ title: 'Passkey tidak didukung', description: 'Gunakan browser atau perangkat yang mendukung WebAuthn.' });
                    return;
                }

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]').content;
                    const optionsResponse = await fetch('{{ route('admin.profile.security.passkeys.options') }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } });
                    const options = await optionsResponse.json();
                    options.challenge = toBuffer(options.challenge);
                    options.user.id = toBuffer(options.user.id);
                    options.excludeCredentials = (options.excludeCredentials ?? []).map((credential) => ({ ...credential, id: toBuffer(credential.id) }));
                    const credential = await navigator.credentials.create({ publicKey: options });
                    const response = credential.response;
                    const registrationResponse = await fetch('{{ route('admin.profile.security.passkeys.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ id: credential.id, rawId: toBase64Url(credential.rawId), type: credential.type, name, response: { clientDataJSON: toBase64Url(response.clientDataJSON), attestationObject: toBase64Url(response.attestationObject), transports: response.getTransports ? response.getTransports() : [] } }) });
                    const result = await registrationResponse.json();
                    if (!registrationResponse.ok || result.status !== 'success') throw new Error(result.message || 'Passkey registration failed.');
                    window.location.reload();
                } catch (error) {
                    if (!['NotAllowedError', 'AbortError'].includes(error.name)) window.AppPopup?.error({ title: 'Passkey gagal didaftarkan', description: 'Coba lagi dari perangkat yang mendukung passkey.' });
                }
            });
        })();
    </script>
</x-dashboard.layout>
