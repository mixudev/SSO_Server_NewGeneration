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
            @can('keys.view')
                <x-form.button href="{{ route('admin.keys.index') }}" variant="primary" size="md" icon="arrow-repeat" id="btn-rotate-keys">
                    Kelola Kunci Keamanan
                </x-form.button>
            @endcan

            @can('audit.view')
                <x-form.button href="{{ route('admin.audit.index') }}" variant="secondary" size="md" icon="shield-lock" id="btn-audit-log">
                    Buka Audit Log
                </x-form.button>
            @endcan

            @can('sessions.view')
                <x-form.button href="{{ route('admin.sessions.index') }}" variant="outline" size="md" icon="pc-display" id="btn-sessions">
                    Lihat Sesi Aktif
                </x-form.button>
            @endcan
        </div>
    </div>
    <div class="dashboard-hero-mark" aria-hidden="true">
        <i class="bi bi-shield-check"></i>
    </div>
</section>
