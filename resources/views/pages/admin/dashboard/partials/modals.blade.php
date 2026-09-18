<!-- Modal: Key Rotation Using AppModal -->
<x-app-modal
    id="rotateKeyModal"
    maxWidth="md"
    title="Rotasi Kunci Keamanan SSO"
    description="Perbarui pasangan kunci RSA/EC yang digunakan untuk menandatangani JWT & OIDC tokens."
    icon="key"
    iconColor="violet"
>
    <form id="rotateKeyForm" onsubmit="event.preventDefault(); submitRotateKeys();" style="display: flex; flex-direction: column; gap: 14px;">
        <div>
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--dash-text-heading); margin-bottom: 6px;">
                Algoritma Penandatanganan
            </label>
            <select id="keyAlgorithm" style="width: 100%; border-radius: 6px; border: 1px solid var(--dash-border); background: var(--dash-card); color: var(--dash-text); padding: 9px 12px; font-size: 13px; outline: none;">
                <option value="RS256">RS256 (RSA Signature with SHA-256) — Standard</option>
                <option value="ES256">ES256 (ECDSA using P-256 and SHA-256)</option>
                <option value="RS512">RS512 (RSA Signature with SHA-512)</option>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--dash-text-heading); margin-bottom: 6px;">
                Masa Transisi Kunci Lama (Grace Period)
            </label>
            <select id="gracePeriod" style="width: 100%; border-radius: 6px; border: 1px solid var(--dash-border); background: var(--dash-card); color: var(--dash-text); padding: 9px 12px; font-size: 13px; outline: none;">
                <option value="24">24 Jam (Disarankan untuk meminimalkan dampak klien)</option>
                <option value="12">12 Jam</option>
                <option value="0">Segera Batalkan Kunci Lama (Immediate)</option>
            </select>
        </div>

        <div style="border-radius: 6px; background-color: var(--dash-warning-soft); border: 1px solid rgba(245,158,11,0.25); padding: 12px; font-size: 12px; color: var(--dash-warning); line-height: 1.5;">
            <strong>Perhatian:</strong> Klien yang menyimpan cache JWKS akan membutuhkan waktu beberapa saat untuk memperbarui public key terbaru.
        </div>
    </form>

    <x-slot name="footer">
        <button
            type="button"
            onclick="AppModal.close('rotateKeyModal')"
            class="modal-btn-cancel rounded-none"
        >
            Batal
        </button>
        <button
            type="button"
            onclick="submitRotateKeys()"
            class="modal-btn-primary rounded-none"
        >
            Konfirmasi &amp; Rotasi
        </button>
    </x-slot>
</x-app-modal>

<!-- Script for Actions with Confirmations -->
<script>
    function confirmClearCache() {
        window.confirmAction({
            type: 'warning',
            title: 'Bersihkan Cache Identitas?',
            description: 'Tindakan ini akan menghapus cache izin, user profile claims, dan token metadata di seluruh cluster redis/database.',
            confirmText: 'Ya, Bersihkan',
            cancelText: 'Batal',
            onConfirm: function() {
                AppPopup.success({
                    title: 'Cache Berhasil Dibersihkan',
                    description: 'Semua cache identitas dan session metadata telah diperbarui.'
                });
            }
        });
    }

    function confirmRevokeTokens() {
        window.confirmAction({
            type: 'delete',
            title: 'Cabut Semua Sesi Aktif?',
            description: 'PERINGATAN: Semua token akses dan refresh token aktif milik seluruh pengguna akan segera dibatalkan. Pengguna harus login ulang.',
            confirmText: 'Ya, Cabut Semua',
            cancelText: 'Batalkan',
            onConfirm: function() {
                AppPopup.warning({
                    title: 'Sesi Telah Dicabut',
                    description: 'Seluruh refresh token aktif telah dibatalkan dari database.'
                });
            }
        });
    }

    function submitRotateKeys() {
        const algo = document.getElementById('keyAlgorithm')?.value || 'RS256';
        AppModal.close('rotateKeyModal');

        setTimeout(() => {
            window.confirmAction({
                title: 'Verifikasi Final Rotasi Kunci',
                description: 'Anda akan menerbitkan pasangan kunci baru dengan algoritma ' + algo + '. Pastikan proses ini telah dikoordinasikan.',
                confirmText: 'Terbitkan Kunci',
                cancelText: 'Kembali',
                onConfirm: function() {
                    AppPopup.success({
                        title: 'Kunci Berhasil Dirotasi',
                        description: 'Pasangan kunci penandatanganan baru (' + algo + ') aktif dan JWKS telah diperbarui.'
                    });
                }
            });
        }, 300);
    }
</script>
