<!-- Posture & Action Queue (2 Columns) -->
<section class="dashboard-grid dashboard-grid-two dashboard-section-gap">
    <x-dashboard.card aria-labelledby="security-posture-heading">
        <div class="dashboard-card-heading">
            <div>
                <span class="dashboard-stat-kicker">Security posture</span>
                <h3 id="security-posture-heading">Control coverage</h3>
            </div>
            <span class="dashboard-shield-badge" title="Tingkat Kepatuhan Keamanan">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3 5 6v5c0 4.6 2.9 8.6 7 10 4.1-1.4 7-5.4 7-10V6l-7-3Z"/>
                </svg>
            </span>
        </div>
        <ul class="dashboard-check-list">
            <li>
                <span class="status-dot" style="margin-top: 5px;"></span>
                <div>
                    <strong>Authentication boundary</strong>
                    <small>Paket autentikasi terpusat aktif dengan validasi sesi terenkripsi.</small>
                </div>
            </li>
            <li>
                <span class="status-dot" style="margin-top: 5px;"></span>
                <div>
                    <strong>Server-side authorization</strong>
                    <small>Pengecekan permission wajib di seluruh rute administrasi SSO.</small>
                </div>
            </li>
            <li>
                <span class="status-dot" style="margin-top: 5px;"></span>
                <div>
                    <strong>Audit readiness</strong>
                    <small>Security event logging siap menangkap audit jejak otentikasi.</small>
                </div>
            </li>
        </ul>
    </x-dashboard.card>

    <x-dashboard.card aria-labelledby="next-actions-heading">
        <div class="dashboard-card-heading">
            <div>
                <span class="dashboard-stat-kicker">Operator queue</span>
                <h3 id="next-actions-heading">Next actions</h3>
            </div>
            <span class="dashboard-count">03 Tasks</span>
        </div>
        <ol class="dashboard-action-list">
            <li>
                <span class="step-number">01</span>
                <div>
                    <strong>Register an organization</strong>
                    <small>Tentukan batas tenant dan domain otentikasi pertama.</small>
                </div>
            </li>
            <li>
                <span class="step-number">02</span>
                <div>
                    <strong>Create a client application</strong>
                    <small>Terbitkan kredensial OAuth setelah review redirect URL.</small>
                </div>
            </li>
            <li>
                <span class="step-number">03</span>
                <div>
                    <strong>Configure scopes & claims</strong>
                    <small>Pastikan prinsip hak akses terendah (least privilege) terpenuhi.</small>
                </div>
            </li>
        </ol>
    </x-dashboard.card>
</section>
