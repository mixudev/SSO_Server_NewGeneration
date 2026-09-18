<!-- TailAdmin Hero Section -->
<section class="dashboard-hero" aria-labelledby="dashboard-heading">
    <div>
        <div class="dashboard-hero-eyebrow">
            <span class="dashboard-live-dot"></span>
            <span>Security operations</span>
        </div>
        <h2 id="dashboard-heading">Identity platform overview</h2>
        <p>
            Kelola kontrol akses terpusat untuk aplikasi klien terdaftar, kebijakan izin berbasis peran (RBAC), serta pemantauan otentikasi federasi SSO/OIDC secara real-time.
        </p>
        <div class="dashboard-hero-actions">
            <!-- Action 1: Modal Trigger -->
            <x-form.button
                variant="primary"
                size="md"
                icon="arrow-repeat"
                onclick="AppModal.open('rotateKeyModal')"
                id="btn-rotate-keys"
            >
                Rotasi Kunci Keamanan
            </x-form.button>

            <!-- Action 2: Alert Confirm (Cache) -->
            <x-form.button
                variant="secondary"
                size="md"
                icon="eraser"
                onclick="confirmClearCache()"
                id="btn-flush-cache"
            >
                Flush Identity Cache
            </x-form.button>

            <!-- Action 3: Danger Alert Confirm (Revoke) -->
            <x-form.button
                variant="outline"
                size="md"
                icon="slash-circle"
                onclick="confirmRevokeTokens()"
                id="btn-revoke-sessions"
            >
                Cabut Sesi Aktif
            </x-form.button>
        </div>
    </div>
    <div class="dashboard-hero-mark" aria-hidden="true">
        <i class="bi bi-shield-check"></i>
    </div>
</section>
