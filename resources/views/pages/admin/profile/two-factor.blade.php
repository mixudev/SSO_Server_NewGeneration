<x-dashboard.layout
    title="Set up two-factor authentication"
    breadcrumb="Account / Security / 2FA"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-start gap-4">
            <x-form.button href="{{ route('admin.profile.security') }}" variant="ghost" size="sm" icon="arrow-left" aria-label="Back to security center">Back to security center</x-form.button>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--dash-primary)]">Account protection</p>
                <h1 class="mt-2 text-2xl font-semibold text-[var(--dash-text-heading)]">Set up two-factor authentication</h1>
                <p class="mt-1 max-w-2xl text-sm text-[var(--dash-text-muted)]">Add a second verification step with your authenticator app. Scan the code, then verify one code to finish setup.</p>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="flex min-h-[420px] flex-col border border-[var(--dash-border)] bg-[var(--dash-card)]">
                <header class="flex items-start justify-between border-b border-[var(--dash-border)] px-6 py-5">
                    <div><p class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--dash-primary)]">Step 01</p><h2 class="mt-1 text-lg font-semibold text-[var(--dash-text-heading)]">Scan QR code</h2><p class="mt-1 text-sm text-[var(--dash-text-muted)]">Open your authenticator app and scan this code.</p></div>
                    <i class="bi bi-qr-code text-2xl text-[var(--dash-primary)]" aria-hidden="true"></i>
                </header>
                <div class="flex flex-1 flex-col items-center justify-center px-6 py-8">
                    <div class="flex h-56 w-56 items-center justify-center border border-[var(--dash-border)] bg-white p-4 shadow-[0_0_0_8px_var(--dash-primary-soft)]" aria-label="Two-factor setup QR code">{!! $setup['qr_code_svg'] !!}</div>
                    <p class="mt-7 text-center text-xs text-[var(--dash-text-muted)]">Can’t scan? Enter the setup key manually.</p>
                    <code class="mt-2 max-w-full break-all border border-dashed border-[var(--dash-border)] bg-[var(--dash-body)] px-3 py-2 text-center text-xs text-[var(--dash-text)]">{{ $setup['secret'] }}</code>
                </div>
            </section>

            <section class="flex min-h-[420px] flex-col border border-[var(--dash-border)] bg-[var(--dash-card)]">
                <header class="flex items-start justify-between border-b border-[var(--dash-border)] px-6 py-5">
                    <div><p class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--dash-primary)]">Step 02</p><h2 class="mt-1 text-lg font-semibold text-[var(--dash-text-heading)]">Verify your code</h2><p class="mt-1 text-sm text-[var(--dash-text-muted)]">Enter the current one-time code from your app.</p></div>
                    <i class="bi bi-shield-check text-2xl text-[var(--dash-primary)]" aria-hidden="true"></i>
                </header>
                <form method="POST" action="{{ route('admin.profile.security.two-factor.confirm') }}" class="flex flex-1 flex-col justify-between px-6 py-8">
                    @csrf
                    <div>
                        <label for="code" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-[var(--dash-text-muted)]">One-time code</label>
                        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus placeholder="000000" class="w-full border border-[var(--dash-border)] bg-[var(--dash-body)] px-4 py-4 text-center text-2xl tracking-[0.55em] text-[var(--dash-text-heading)] outline-none transition focus:border-[var(--dash-primary)] focus:ring-2 focus:ring-[var(--dash-primary-ring)]" aria-describedby="code-help">
                        <p id="code-help" class="mt-3 text-xs leading-5 text-[var(--dash-text-muted)]">Use the six-digit code currently shown by your authenticator app. Codes are valid for a short time and can only be used once.</p>
                        @error('code')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <footer class="mt-8 flex items-center justify-between gap-3 border-t border-[var(--dash-border)] pt-5"><x-form.button href="{{ route('admin.profile.security') }}" variant="secondary" size="sm">Cancel</x-form.button><x-form.button type="submit" size="sm" icon="check2-circle">Enable 2FA</x-form.button></footer>
                </form>
            </section>
        </div>

        @if (!empty($recoveryCodes))
            <section class="recovery-panel p-6">
                <div class="flex items-start gap-3"><i class="bi bi-key text-xl text-[var(--dash-warning)]" aria-hidden="true"></i><div><h2 class="text-base font-semibold text-[var(--dash-text-heading)]">Save your recovery codes</h2><p class="mt-1 text-sm text-[var(--dash-text)]">These one-time backup codes are shown only during setup. Store them in a password manager or another secure location. Each code works once.</p></div></div>
                <div id="recovery-codes" class="recovery-codes mt-5 grid gap-2 p-4 font-mono text-sm font-semibold sm:grid-cols-2">
                    @foreach ($recoveryCodes as $recoveryCode)
                        <span>{{ $recoveryCode }}</span>
                    @endforeach
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                    <x-form.button type="button" id="download-recovery-codes" variant="secondary" size="sm" icon="download" class="recovery-action">Download codes</x-form.button>
                    <x-form.button type="button" id="copy-recovery-codes" variant="secondary" size="sm" icon="copy" class="recovery-action">Copy codes</x-form.button>
                </div>
            </section>
            <script>
                (() => {
                    const codes = @json($recoveryCodes);
                    document.getElementById('download-recovery-codes')?.addEventListener('click', () => {
                        const blob = new Blob([`{{ config('app.name') }} two-factor recovery codes\n\n${codes.join('\n')}\n`], { type: 'text/plain' });
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = 'two-factor-recovery-codes.txt';
                        link.click();
                        URL.revokeObjectURL(link.href);
                    });
                    document.getElementById('copy-recovery-codes')?.addEventListener('click', async () => {
                        await navigator.clipboard.writeText(codes.join('\n'));
                        window.AppPopup?.success({ title: 'Recovery codes copied' });
                    });
                })();
            </script>
        @endif
    </div>
</x-dashboard.layout>