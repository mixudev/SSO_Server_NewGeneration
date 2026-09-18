<x-dashboard.layout
    title="My Profile"
    breadcrumb="Account / Profile"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <div class="space-y-6">

        <section class="relative overflow-hidden border border-[var(--dash-border)] bg-[var(--dash-card)]">
            <div class="profile-banner h-40"></div>
            <div class="relative px-6 pb-6 sm:px-8">
                <div class="-mt-14 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex items-end gap-4">
                        <form method="POST" action="{{ route('admin.profile.avatar.update') }}" enctype="multipart/form-data" id="avatar-form">
                            @csrf
                            <label for="avatar" class="group relative block h-28 w-28 cursor-pointer overflow-hidden rounded-full border-4 border-[var(--dash-card)] bg-[var(--dash-primary-soft)] shadow-sm" title="Change profile photo">
                                @if ($user->avatar_path)
                                    <img src="{{ Storage::disk('public')->url($user->avatar_path) }}" alt="{{ $user->name }} profile photo" class="h-full w-full object-cover">
                                @else
                                    <span class="flex h-full w-full items-center justify-center text-4xl font-semibold text-[var(--dash-primary)]"><i class="bi bi-person" aria-hidden="true"></i></span>
                                @endif
                                <span class="absolute inset-0 flex items-center justify-center bg-black/55 text-xs font-semibold text-white opacity-0 transition group-hover:opacity-100 group-focus-within:opacity-100"><i class="bi bi-camera mr-1" aria-hidden="true"></i>Change</span>
                            </label>
                            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" onchange="confirmAction({ type: 'confirm', title: 'Change profile photo?', description: 'The selected image will replace your current profile photo.', confirmText: 'Change photo', onConfirm: () => document.getElementById('avatar-form').submit() })">
                        </form>
                        <div class="pb-1">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Account center</p>
                            <h1 class="mt-1 text-2xl font-semibold leading-tight text-[var(--dash-text-heading)]">{{ $user->name }}</h1>
                            <p class="mt-1 text-[13px] text-[var(--dash-text-muted)]">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.profile.edit') }}" class="inline-flex items-center gap-2 border border-[var(--dash-border)] px-3 py-2 text-sm font-semibold text-[var(--dash-text)] transition hover:border-[var(--dash-primary)]"><i class="bi bi-pencil" aria-hidden="true"></i>Edit profile</a>
                        @if ($user->avatar_path)
                            <form method="POST" action="{{ route('admin.profile.avatar.destroy') }}" data-confirm="Foto profil akan dihapus. Lanjutkan?" data-confirm-type="delete" data-confirm-title="Hapus foto profil" data-confirm-btn="Hapus foto">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-2 border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50"><i class="bi bi-trash3" aria-hidden="true"></i>Remove photo</button></form>
                        @endif
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($roles as $role)<span class="bg-[var(--dash-primary-soft)] px-2.5 py-1 text-xs font-semibold text-[var(--dash-primary)]">{{ str_replace('_', ' ', $role) }}</span>@endforeach
                    <span class="{{ $user->email_verified_at ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} px-2.5 py-1 text-xs font-semibold"><i class="bi {{ $user->email_verified_at ? 'bi-check-circle' : 'bi-exclamation-circle' }} mr-1" aria-hidden="true"></i>{{ $user->email_verified_at ? 'Verified email' : 'Email verification pending' }}</span>
                </div>
            </div>
        </section>

        <div class="grid items-stretch gap-6 lg:grid-cols-[minmax(240px,0.75fr)_minmax(0,1.8fr)]">
            <section class="flex flex-col border border-[var(--dash-border)] bg-[var(--dash-card)]">
                <header class="border-b border-[var(--dash-border)] px-6 py-5"><p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Identity</p><h2 class="mt-1 text-lg font-semibold text-[var(--dash-text-heading)]">User information</h2><p class="mt-1 text-sm text-[var(--dash-text-muted)]">The identity used across the control plane.</p></header>
                <dl class="flex-1 space-y-5 px-6 py-6">
                    <div><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--dash-text-muted)]">Full name</dt><dd class="mt-1 text-sm font-medium text-[var(--dash-text-heading)]">{{ $user->name }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--dash-text-muted)]">Email address</dt><dd class="mt-1 break-all text-sm font-medium text-[var(--dash-text-heading)]">{{ $user->email }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--dash-text-muted)]">Member since</dt><dd class="mt-1 text-sm font-medium text-[var(--dash-text-heading)]">{{ $user->created_at?->format('d M Y') ?? 'Not available' }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--dash-text-muted)]">Assigned roles</dt><dd class="mt-1 text-sm font-medium text-[var(--dash-text-heading)]">{{ $roles->count() }} role(s)</dd></div>
                </dl>
                <footer class="border-t border-[var(--dash-border)] px-6 py-4"><a href="{{ route('admin.profile.edit') }}" class="text-sm font-semibold text-[var(--dash-primary)]">Edit identity <i class="bi bi-arrow-right ml-1" aria-hidden="true"></i></a></footer>
            </section>

            <section class="flex flex-col border border-[var(--dash-border)] bg-[var(--dash-card)]">
                <header class="flex items-start justify-between gap-4 border-b border-[var(--dash-border)] px-6 py-5"><div><p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">Account controls</p><h2 class="mt-1 text-lg font-semibold text-[var(--dash-text-heading)]">Account security</h2><p class="mt-1 text-sm text-[var(--dash-text-muted)]">Review protection status and manage security from one dedicated center.</p></div><i class="bi bi-shield-check text-2xl text-[var(--dash-primary)]" aria-hidden="true"></i></header>
                <div class="grid flex-1 gap-3 p-6 sm:grid-cols-2">
                    <div class="border border-[var(--dash-border)] p-4"><div class="flex items-center justify-between gap-3"><span class="text-[13px] text-[var(--dash-text-muted)]">Email verification</span><i class="bi bi-envelope-check text-lg text-emerald-700" aria-hidden="true"></i></div><p class="mt-3 text-[15px] font-semibold leading-snug text-[var(--dash-text-heading)]">{{ $user->email_verified_at ? 'Verified and active' : 'Verification required' }}</p></div>
                    <div class="border border-[var(--dash-border)] p-4"><div class="flex items-center justify-between gap-3"><span class="text-[13px] text-[var(--dash-text-muted)]">Two-factor authentication</span><i class="bi bi-phone text-lg text-[var(--dash-primary)]" aria-hidden="true"></i></div><p class="mt-3 text-[15px] font-semibold leading-snug text-[var(--dash-text-heading)]">{{ $security['two_factor_enabled'] ? 'Enabled' : 'Not enabled' }}</p></div>
                    <div class="border border-[var(--dash-border)] p-4"><div class="flex items-center justify-between gap-3"><span class="text-[13px] text-[var(--dash-text-muted)]">Passkeys</span><i class="bi bi-fingerprint text-lg text-[var(--dash-primary)]" aria-hidden="true"></i></div><p class="mt-3 text-[15px] font-semibold leading-snug text-[var(--dash-text-heading)]">{{ count($security['passkeys']) }} registered device(s)</p></div>
                    <div class="border border-[var(--dash-border)] p-4"><div class="flex items-center justify-between gap-3"><span class="text-[13px] text-[var(--dash-text-muted)]">Active sessions</span><i class="bi bi-laptop text-lg text-[var(--dash-primary)]" aria-hidden="true"></i></div><p class="mt-3 text-[15px] font-semibold leading-snug text-[var(--dash-text-heading)]">{{ count($security['sessions']) }} active device(s)</p></div>
                </div>
                <footer class="border-t border-[var(--dash-border)] px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--dash-text-muted)]">Security control</span>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" onclick="AppModal.open('password-management-modal')" class="inline-flex items-center justify-center border border-[var(--dash-border)] px-3 py-2 text-sm font-semibold text-[var(--dash-text)] transition hover:border-[var(--dash-primary)]">Manage password</button>
                            <a href="{{ route('admin.profile.security') }}" class="inline-flex items-center justify-center gap-2 bg-[var(--dash-primary)] px-3 py-2 text-sm font-semibold text-white transition hover:opacity-90">Open security center <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </footer>
            </section>
        </div>
    </div>

    <x-app-modal id="password-management-modal" maxWidth="lg" title="Manage account password" description="Change your password or request a secure reset link." icon="key" iconColor="indigo">
        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.profile.password.update') }}" id="password-update-form" class="space-y-4">
                @csrf
                <div>
                    <label for="current_password">Current password</label>
                    <div class="relative">
                        <input id="current_password" name="current_password" type="password" required autocomplete="current-password" placeholder="Enter your current password" class="pr-10">
                        <button type="button" class="password-toggle absolute inset-y-0 right-0 px-3 text-[var(--dash-text-muted)]" data-target="current_password" aria-label="Show current password"><i class="bi bi-eye" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password">New password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters" class="pr-10">
                            <button type="button" class="password-toggle absolute inset-y-0 right-0 px-3 text-[var(--dash-text-muted)]" data-target="password" aria-label="Show new password"><i class="bi bi-eye" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmation">Confirm new password</label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters" class="pr-10">
                            <button type="button" class="password-toggle absolute inset-y-0 right-0 px-3 text-[var(--dash-text-muted)]" data-target="password_confirmation" aria-label="Show password confirmation"><i class="bi bi-eye" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="border-t border-[var(--dash-border)] pt-5">
                <p class="text-sm font-semibold text-[var(--dash-text-heading)]">Need a reset link?</p>
                <p class="mt-1 text-xs text-[var(--dash-text-muted)]">A secure, time-limited link will be sent to {{ $user->email }}.</p>
                <form method="POST" action="{{ route('admin.profile.security.password-reset.send') }}" class="mt-3" data-confirm="Kirim link reset password ke alamat email terverifikasi Anda?" data-confirm-title="Kirim reset link" data-confirm-btn="Kirim link">
                    @csrf
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <button type="submit" class="modal-btn-cancel">Send reset link to email</button>
                </form>
            </div>
        </div>
        <x-slot name="footer"><button type="button" onclick="AppModal.close('password-management-modal')" class="modal-btn-cancel">Cancel</button><button type="button" class="modal-btn-primary" onclick="confirmAction({ type: 'warning', title: 'Update password?', description: 'Your current password will be replaced. Continue?', confirmText: 'Update password', onConfirm: () => document.getElementById('password-update-form').requestSubmit() })">Update password</button></x-slot>
    </x-app-modal>

    <script>
        document.querySelectorAll('.password-toggle').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.dataset.target);
                const icon = toggle.querySelector('i');
                const visible = input.type === 'text';

                input.type = visible ? 'password' : 'text';
                icon.classList.toggle('bi-eye', visible);
                icon.classList.toggle('bi-eye-slash', !visible);
                toggle.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
            });
        });
    </script>
</x-dashboard.layout>