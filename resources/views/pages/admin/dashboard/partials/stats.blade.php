<!-- Platform Domains (3 Columns) -->
<section class="dashboard-grid dashboard-grid-three" aria-label="Platform domains">
    <x-dashboard.card class="dashboard-stat-card">
        <span class="dashboard-stat-kicker">01 / Foundation</span>
        <strong>Identity registry</strong>
        <p>Organisasi, aplikasi klien terdaftar, scopes, serta claims siap dikelola secara terpusat.</p>
        <span class="dashboard-status-badge status-ready">
            <span class="status-dot"></span>
            <span>Operational</span>
        </span>
    </x-dashboard.card>

    <x-dashboard.card class="dashboard-stat-card">
        <span class="dashboard-stat-kicker">02 / Access Control</span>
        <strong>Authorization layer</strong>
        <p>Boundary kebijakan peran (RBAC) dan izin melindungi seluruh antarmuka operasional.</p>
        <span class="dashboard-status-badge status-enforced">
            <span class="status-dot"></span>
            <span>Enforced</span>
        </span>
    </x-dashboard.card>

    <x-dashboard.card class="dashboard-stat-card">
        <span class="dashboard-stat-kicker">03 / Federation Boundary</span>
        <strong>OAuth2 / OIDC Engine</strong>
        <p>Passport mengamankan token boundary untuk aplikasi klien terpercaya dengan PKCE.</p>
        <span class="dashboard-status-badge status-review">
            <span class="status-dot status-dot-amber"></span>
            <span>Review Active</span>
        </span>
    </x-dashboard.card>
</section>
