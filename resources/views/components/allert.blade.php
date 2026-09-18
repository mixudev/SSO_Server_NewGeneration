{{--
|--------------------------------------------------------------------------
| AppPopup Component — Full Tailwind CSS Edition
|--------------------------------------------------------------------------
| Simpan di: resources/views/components/allert.blade.php
| Include SEKALI di layout utama (sebelum </body>):
|   <x-allert />
|
| Pastikan tailwind.config.js menyertakan path komponen ini:
|   content: ['./resources/views/**/*.blade.php']
|
| -----------------------------------------------------------------------
| A. OTOMATIS — dari session flash Controller
| -----------------------------------------------------------------------
|
|   return redirect()->back()->with('success', 'Data berhasil disimpan.');
|   return redirect()->back()->with('error',   'Terjadi kesalahan.');
|   return redirect()->back()->with('warning', 'Stok hampir habis.');
|   return redirect()->back()->with('info',    'Fitur ini masih beta.');
|
|   // withErrors — pesan pertama tampil sebagai popup error:
|   return redirect()->back()->withErrors(['email' => 'Email sudah terdaftar.']);
|
|   // Dengan judul kustom (format "Judul|Deskripsi"):
|   return redirect()->back()->with('success', 'Tersimpan!|Data kamu berhasil diperbarui.');
|
| -----------------------------------------------------------------------
| B. MANUAL — dari JavaScript
| -----------------------------------------------------------------------
|
|   AppPopup.success({ title: 'Berhasil!', description: 'Data tersimpan.' });
|   AppPopup.error({ title: 'Gagal', description: 'Terjadi kesalahan.', confirmText: 'Coba Lagi', cancelText: 'Batal' });
|   AppPopup.warning({ title: 'Perhatian', description: 'Stok hampir habis.', confirmText: 'Restok', cancelText: 'Nanti' });
|   AppPopup.info({ title: 'Info', description: 'Sedang dalam beta.' });
|   AppPopup.confirm({
|       title       : 'Hapus data ini?',
|       description : 'Tindakan ini tidak dapat dibatalkan.',
|       confirmText : 'Ya, Hapus',
|       cancelText  : 'Batal',
|       onConfirm   : () => document.getElementById('delete-form').submit(),
|       onCancel    : () => console.log('dibatalkan'),
|   });
|   AppPopup.close();
|
| -----------------------------------------------------------------------
| CATATAN PERILAKU:
|   - success  : TANPA tombol, auto-close 2.5 detik + progress bar
|   - error    : dengan tombol, harus ditutup manual
|   - warning  : dengan tombol, harus ditutup manual
|   - info     : dengan tombol, harus ditutup manual
|   - confirm  : dua tombol (confirm + cancel)
|   - Klik backdrop / tekan Escape / tombol X → tutup popup
|   - Dark mode: class .dark pada <html> (Tailwind dark mode)
--}}

{{-- =====================================================================
     KEYFRAME ANIMATIONS — hanya @keyframes, tidak bisa diganti Tailwind
     ===================================================================== --}}
@once
<style>
@keyframes popupOverlayIn  { from { opacity: 0 } to { opacity: 1 } }
@keyframes popupOverlayOut { from { opacity: 1 } to { opacity: 0 } }
@keyframes popupBoxIn      { from { opacity: 0; transform: translate3d(0, 8px, 0) scale(.98) } to { opacity: 1; transform: translate3d(0, 0, 0) scale(1) } }
@keyframes popupBoxOut     { from { opacity: 1; transform: translate3d(0, 0, 0) scale(1) } to { opacity: 0; transform: translate3d(0, 6px, 0) scale(.98) } }
@keyframes popupIconPop    { from { opacity: 0; transform: scale(.92) } to { opacity: 1; transform: scale(1) } }
@keyframes popupRingPulse  { 0% { transform: scale(0.95); opacity: 0.8; } 100% { transform: scale(1.6); opacity: 0; } }
@keyframes popupSvgIn      { from { opacity: 0; transform: scale(.5) rotate(-10deg) } to { opacity: 1; transform: scale(1) rotate(0) } }
@keyframes popupSlideUp    { from { opacity: 0; transform: translateY(10px) } to { opacity: 1; transform: translateY(0) } }

.popup-anim-overlay-in  { animation: popupOverlayIn .12s ease-out both }
.popup-anim-overlay-out { animation: popupOverlayOut .12s ease-in both }
.popup-anim-box-in      { animation: popupBoxIn .18s ease-out both; will-change: transform, opacity }
.popup-anim-box-out     { animation: popupBoxOut .14s ease-in both }
.popup-anim-icon        { animation: popupIconPop .14s ease-out both }
.popup-anim-ring        { animation: popupRingPulse 1.8s cubic-bezier(0, 0, 0.2, 1) infinite }
.popup-anim-svg         { animation: none }
.popup-anim-title       { animation: none }
.popup-anim-desc        { animation: none }
.popup-anim-actions     { animation: none }
#popup-close-x          { transition: background .15s, color .15s, transform .2s ease }
#popup-close-x:hover    { transform: rotate(90deg) }
</style>
@endonce

{{-- =====================================================================
     HTML — semua styling menggunakan Tailwind utility classes
     ===================================================================== --}}
<div
    id="allert"
    role="dialog"
    aria-modal="true"
    aria-labelledby="popup-title"
    aria-describedby="popup-desc"
    class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
>
    {{-- Backdrop --}}
    <div
        id="popup-backdrop"
        class="absolute inset-0 bg-black/50"
    ></div>

    {{-- Card --}}
    <div
        id="popup-box"
        class="relative w-full max-w-[420px] mx-auto overflow-hidden pt-8 px-6 pb-6
                       bg-[var(--dash-card)] border border-[var(--dash-border)]
                       text-[var(--dash-text)] shadow-[0_20px_60px_-10px_rgba(0,0,0,0.18)]
                       dark:shadow-[0_20px_60px_-10px_rgba(0,0,0,0.65)]"
    >
        {{-- Top accent bar — background color di-set JS --}}
        <div
            id="popup-accent-bar"
            class="absolute top-0 left-0 right-0 h-[3px] rounded-t-2xl transition-colors duration-300"
        ></div>

        {{-- Close button --}}
        <button
            id="popup-close-x"
            type="button"
            aria-label="Tutup"
            class="absolute top-3.5 right-3.5 w-8 h-8 flex items-center justify-center
                   rounded-[10px] bg-transparent border-0 cursor-pointer
                   text-gray-400 hover:bg-gray-100 hover:text-gray-700
                   dark:text-[var(--dash-muted)] dark:hover:bg-[var(--dash-card-hover)] dark:hover:text-[var(--dash-text-heading)]
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-300"
        >
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        {{-- Icon --}}
        <div id="popup-icon" class="flex justify-center mb-5"></div>

        {{-- Title --}}
        <h3
            id="popup-title"
            class="popup-anim-title text-center text-xl font-bold leading-snug tracking-tight mb-2
                   text-[var(--dash-text-heading)]"
        ></h3>

        {{-- Description --}}
        <p
            id="popup-desc"
            class="popup-anim-desc text-center text-sm leading-relaxed mb-6 hidden
                   text-[var(--dash-muted)]"
        ></p>

        {{-- Action buttons --}}
        <div id="popup-actions" class="popup-anim-actions flex gap-2.5 hidden"></div>

        {{-- Progress bar — background color di-set JS --}}
        <div
            id="popup-progress"
            class="absolute bottom-0 left-0 h-[3px] w-full rounded-b-2xl opacity-50 hidden"
        ></div>
    </div>
</div>

@once
<script>
(function () {
    'use strict';

    /* ------------------------------------------------------------------
       CONFIG PER TYPE — semua pakai Tailwind class string
    ------------------------------------------------------------------ */
    const CONFIGS = {
        success: {
            accent    : '#22c55e',
            ring      : 'rgba(34,197,94,0.25)',
            iconBg    : 'bg-green-100 dark:bg-green-950',
            iconColor : 'text-green-600 dark:text-green-400',
            btnClass  : 'bg-green-500 hover:bg-green-600 focus-visible:ring-green-500',
            svg: `<svg class="popup-anim-svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>`,
        },
        error: {
            accent    : '#ef4444',
            ring      : 'rgba(239,68,68,0.25)',
            iconBg    : 'bg-red-100 dark:bg-red-950',
            iconColor : 'text-red-600 dark:text-red-400',
            btnClass  : 'bg-red-500 hover:bg-red-600 focus-visible:ring-red-500',
            svg: `<svg class="popup-anim-svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>`,
        },
        warning: {
            accent    : '#f59e0b',
            ring      : 'rgba(245,158,11,0.25)',
            iconBg    : 'bg-amber-100 dark:bg-amber-950',
            iconColor : 'text-amber-600 dark:text-amber-400',
            btnClass  : 'bg-amber-500 hover:bg-amber-600 focus-visible:ring-amber-500',
            svg: `<svg class="popup-anim-svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" viewBox="0 0 24 24">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17" stroke-width="3.5"/>
                  </svg>`,
        },
        info: {
            accent    : '#3b82f6',
            ring      : 'rgba(59,130,246,0.25)',
            iconBg    : 'bg-blue-100 dark:bg-blue-950',
            iconColor : 'text-blue-600 dark:text-blue-400',
            btnClass  : 'bg-blue-500 hover:bg-blue-600 focus-visible:ring-blue-500',
            svg: `<svg class="popup-anim-svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="8" stroke-width="3.5"/>
                    <line x1="12" y1="12" x2="12" y2="16"/>
                  </svg>`,
        },
        confirm: {
            accent    : '#6366f1',
            ring      : 'rgba(99,102,241,0.25)',
            iconBg    : 'bg-indigo-100 dark:bg-indigo-950',
            iconColor : 'text-indigo-600 dark:text-indigo-400',
            btnClass  : 'bg-indigo-600 hover:bg-indigo-700 focus-visible:ring-indigo-500',
            svg: `<svg class="popup-anim-svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17" stroke-width="3"/>
                  </svg>`,
        },
        delete: {
            accent    : '#ef4444',
            ring      : 'rgba(239,68,68,0.25)',
            iconBg    : 'bg-red-100 dark:bg-red-950',
            iconColor : 'text-red-600 dark:text-red-400',
            btnClass  : 'bg-red-600 hover:bg-red-700 focus-visible:ring-red-500',
            svg: `<svg class="popup-anim-svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M3 6h18"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                  </svg>`,
        },
        logout: {
            accent    : '#f97316',
            ring      : 'rgba(249,115,22,0.25)',
            iconBg    : 'bg-orange-100 dark:bg-orange-950',
            iconColor : 'text-orange-600 dark:text-orange-400',
            btnClass  : 'bg-orange-600 hover:bg-orange-700 focus-visible:ring-orange-500',
            svg: `<svg class="popup-anim-svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                  </svg>`,
        },
        custom: {
            accent    : '#6366f1',
            ring      : 'rgba(99,102,241,0.25)',
            iconBg    : 'bg-indigo-100 dark:bg-indigo-950',
            iconColor : 'text-indigo-600 dark:text-indigo-400',
            btnClass  : 'bg-indigo-500 hover:bg-indigo-600 focus-visible:ring-indigo-500',
            svg: `<svg class="popup-anim-svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="3.5"/>
                  </svg>`,
        },
    };

    /* ------------------------------------------------------------------
       DOM REFS
    ------------------------------------------------------------------ */
    const $overlay   = document.getElementById('allert');
    const $backdrop  = document.getElementById('popup-backdrop');
    const $box       = document.getElementById('popup-box');
    const $accentBar = document.getElementById('popup-accent-bar');
    const $icon      = document.getElementById('popup-icon');
    const $title     = document.getElementById('popup-title');
    const $desc      = document.getElementById('popup-desc');
    const $actions   = document.getElementById('popup-actions');
    const $closeX    = document.getElementById('popup-close-x');
    const $progress  = document.getElementById('popup-progress');

    let _timer = null, _isOpen = false;

    /* ------------------------------------------------------------------
       OPEN / CLOSE
    ------------------------------------------------------------------ */
    function _open() {
        _isOpen = true;
        $overlay.style.display = 'flex';
        void $overlay.offsetWidth; // reflow paksa trigger animasi

        $overlay.classList.remove('popup-anim-overlay-out');
        $box.classList.remove('popup-anim-box-out');
        $overlay.classList.add('popup-anim-overlay-in');
        $box.classList.add('popup-anim-box-in');

        requestAnimationFrame(() => {
            const btn = $actions.querySelector('button');
            if (btn) btn.focus();
        });
    }

    function _close() {
        if (!_isOpen) return;
        _isOpen = false;
        clearTimeout(_timer);

        $overlay.classList.remove('popup-anim-overlay-in');
        $box.classList.remove('popup-anim-box-in');
        $overlay.classList.add('popup-anim-overlay-out');
        $box.classList.add('popup-anim-box-out');

        // Reset progress bar
        $progress.style.transition = 'none';
        $progress.style.width = '100%';
        $progress.classList.add('hidden');

        setTimeout(() => {
            if (!_isOpen) {
                $overlay.style.display = 'none';
                $overlay.classList.remove('popup-anim-overlay-out');
                $box.classList.remove('popup-anim-box-out');
            }
        }, 280);
    }

    /* ------------------------------------------------------------------
       BUTTON BUILDERS — full Tailwind classes
    ------------------------------------------------------------------ */
    function _makePrimary(text, btnClass, onClickFn) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = text;
        btn.className = [
            'flex-1 py-2 px-3.5 rounded-none border-0 cursor-pointer whitespace-nowrap',
            'text-white text-xs font-semibold tracking-tight shadow-sm',
            'transition-all duration-150',
            'active:scale-[.98]',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
            btnClass,
        ].join(' ');
        btn.addEventListener('click', onClickFn);
        return btn;
    }

    function _makeGhost(text, onClickFn) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = text;
        btn.className = [
            'flex-1 py-2 px-3.5 rounded-none cursor-pointer whitespace-nowrap',
            'border-[1.5px] border-slate-300 dark:border-white/20',
            'bg-white dark:bg-white/[0.04]',
            'text-slate-700 dark:text-slate-200',
            'text-xs font-semibold tracking-tight shadow-sm',
            'transition-all duration-150',
            'hover:bg-slate-50 hover:border-slate-400 hover:text-slate-900',
            'dark:hover:bg-white/[0.08] dark:hover:border-white/30 dark:hover:text-white',
            'active:scale-[.98]',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-slate-400',
        ].join(' ');
        btn.addEventListener('click', onClickFn);
        return btn;
    }

    /* ------------------------------------------------------------------
       CORE: show()
    ------------------------------------------------------------------ */
    function show(opts = {}) {
        const {
            type        = 'info',
            title       = '',
            description = '',
            confirmText = 'Oke, Mengerti',
            cancelText  = null,
            onConfirm   = null,
            onCancel    = null,
            autoClose   = null,
            showButton  = true,
            icon        = null,
            hideIcon    = false,
        } = opts;

        const cfg = CONFIGS[type] ?? CONFIGS.custom;

        /* Warna accent bar & progress bar (hex, tidak bisa pakai Tailwind dinamis) */
        $accentBar.style.backgroundColor = cfg.accent;
        $progress.style.backgroundColor  = cfg.accent;

        /* Re-trigger animasi teks */
        [$title, $desc, $actions].forEach(el => {
            el.style.animation = 'none';
            void el.offsetWidth;
            el.style.animation = '';
        });

        /* Title */
        $title.textContent = title;

        /* Description */
        $desc.innerHTML = description;
        $desc.classList.toggle('hidden', !description);
        $desc.classList.toggle('mb-6', !!description);

        /* Title bottom margin */
        $title.classList.toggle('mb-2', !!description);
        $title.classList.toggle('mb-5', !description && showButton);
        $title.classList.toggle('mb-1', !description && !showButton);

        /* Icon ring */
        $icon.innerHTML = '';
        $icon.classList.toggle('hidden', hideIcon);
        if (!hideIcon) {
            const ringEl = document.createElement('div');
            ringEl.className = `popup-anim-icon relative w-[72px] h-[72px] rounded-full flex items-center justify-center ${cfg.iconBg}`;

            const pulseEl = document.createElement('div');
            pulseEl.className = 'popup-anim-ring absolute inset-[-4px] rounded-full border-2 pointer-events-none';
            pulseEl.style.borderColor = cfg.ring;
            ringEl.appendChild(pulseEl);

            const pulseEl2 = document.createElement('div');
            pulseEl2.className = 'popup-anim-ring absolute inset-[-4px] rounded-full border-2 pointer-events-none';
            pulseEl2.style.borderColor = cfg.ring;
            pulseEl2.style.animationDelay = '0.5s';
            ringEl.appendChild(pulseEl2);

            const svgWrap = document.createElement('div');
            svgWrap.className = cfg.iconColor;
            svgWrap.innerHTML = icon ?? cfg.svg;
            ringEl.appendChild(svgWrap);

            $icon.appendChild(ringEl);
        }

        /* Buttons */
        $actions.innerHTML = '';
        $actions.classList.toggle('hidden', !showButton);
        if (showButton) {
            if (cancelText) {
                $actions.appendChild(_makeGhost(cancelText, () => {
                    _close();
                    if (typeof onCancel === 'function') onCancel();
                }));
            }
            $actions.appendChild(_makePrimary(confirmText, cfg.btnClass, () => {
                _close();
                if (typeof onConfirm === 'function') onConfirm();
            }));
        }

        /* Progress bar */
        clearTimeout(_timer);
        $progress.classList.add('hidden');
        $progress.style.transition = 'none';
        $progress.style.width = '100%';

        if (typeof autoClose === 'number' && autoClose > 0) {
            $progress.classList.remove('hidden');
            void $progress.offsetWidth;
            $progress.style.transition = `width ${autoClose}ms linear`;
            requestAnimationFrame(() => { $progress.style.width = '0%'; });
            _timer = setTimeout(_close, autoClose);
        }

        _open();
    }

    /* ------------------------------------------------------------------
       EVENT LISTENERS
    ------------------------------------------------------------------ */
    $closeX.addEventListener('click', _close);
    $backdrop.addEventListener('click', _close);
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && _isOpen) _close(); });

    /* ------------------------------------------------------------------
       SESSION FLASH HANDLER (Laravel)
    ------------------------------------------------------------------ */
    (function () {
        function parse(raw, defaultTitle) {
            if (!raw) return { title: defaultTitle, description: '' };
            const i = raw.indexOf('|');
            return i !== -1
                ? { title: raw.slice(0, i).trim(), description: raw.slice(i + 1).trim() }
                : { title: defaultTitle, description: raw.trim() };
        }

        @if (session('success'))
            (function () {
                const p = parse(@json(session('success')), 'Berhasil!');
                show({ type: 'success', title: p.title, description: p.description, showButton: false, autoClose: 2500 });
            })();
        @elseif (session('error'))
            (function () {
                const p = parse(@json(session('error')), 'Terjadi Kesalahan');
                show({ type: 'error', title: p.title, description: p.description, confirmText: 'Oke, Mengerti', showButton: true });
            })();
        @elseif (session('status'))
            (function () {
                const p = parse(@json(session('status')), 'Berhasil!');
                show({ type: 'success', title: p.title, description: p.description, showButton: false, autoClose: 3000 });
            })();
        @elseif (session('warning'))
            (function () {
                const p = parse(@json(session('warning')), 'Perhatian');
                show({ type: 'warning', title: p.title, description: p.description, confirmText: 'Oke, Mengerti', showButton: true });
            })();
        @elseif (session('info'))
            (function () {
                const p = parse(@json(session('info')), 'Informasi');
                show({ type: 'info', title: p.title, description: p.description, confirmText: 'Oke, Mengerti', showButton: true });
            })();
        @elseif (isset($errors) && $errors->any())
            (function () {
                const msg = @json($errors->first());
                show({ type: 'error', title: 'Periksa Input Anda', description: msg, confirmText: 'Oke', showButton: true });
            })();
        @endif
    })();

    /* ------------------------------------------------------------------
       PUBLIC API — window.AppPopup
    ------------------------------------------------------------------ */
    window.AppPopup = {
        /**
         * Low-level API — semua opsi tersedia.
         * @param {string}   opts.type         'success'|'error'|'warning'|'info'|'confirm'|'delete'|'logout'|'custom'
         * @param {string}   opts.title        Judul popup
         * @param {string}   opts.description  Deskripsi opsional
         * @param {string}   opts.confirmText  Label tombol utama
         * @param {string}   opts.cancelText   Label tombol batal — jika diisi muncul 2 tombol
         * @param {Function} opts.onConfirm    Callback tombol utama
         * @param {Function} opts.onCancel     Callback tombol batal
         * @param {number}   opts.autoClose    Auto-close setelah N ms
         * @param {boolean}  opts.showButton   false → sembunyikan semua tombol
         * @param {string}   opts.icon         HTML string ikon kustom
         */
        show,

        /** Sukses — tanpa tombol, auto-close 2.5 detik + progress bar */
        success(o = {}) {
            show({ type: 'success', title: o.title ?? 'Berhasil!', description: o.description ?? '',
                   showButton: false, autoClose: o.duration ?? 2500 });
        },

        /** Error — dengan tombol, harus ditutup manual */
        error(o = {}) {
            show({ type: 'error', title: o.title ?? 'Terjadi Kesalahan', description: o.description ?? '',
                   confirmText: o.confirmText ?? 'Oke, Mengerti', cancelText: o.cancelText ?? null,
                   showButton: true, onConfirm: o.onConfirm ?? null, onCancel: o.onCancel ?? null });
        },

        /** Warning — dengan tombol, harus ditutup manual */
        warning(o = {}) {
            show({ type: 'warning', title: o.title ?? 'Perhatian', description: o.description ?? '',
                   confirmText: o.confirmText ?? 'Oke, Mengerti', cancelText: o.cancelText ?? null,
                   showButton: true, onConfirm: o.onConfirm ?? null, onCancel: o.onCancel ?? null });
        },

        /** Info — dengan tombol, harus ditutup manual */
        info(o = {}) {
            show({ type: 'info', title: o.title ?? 'Informasi', description: o.description ?? '',
                   confirmText: o.confirmText ?? 'Oke, Mengerti', cancelText: o.cancelText ?? null,
                   showButton: true, onConfirm: o.onConfirm ?? null, onCancel: o.onCancel ?? null });
        },

        /**
         * Konfirmasi dua tombol — default general confirmation
         * @example
         * AppPopup.confirm({
         *   title: 'Lanjutkan tindakan ini?',
         *   description: 'Perubahan akan segera disimpan.',
         *   confirmText: 'Ya, Lanjutkan',
         *   cancelText: 'Batal',
         *   onConfirm: () => form.submit(),
         * });
         */
        confirm(o = {}) {
            show({
                type: o.type ?? 'confirm',
                title: o.title ?? 'Konfirmasi',
                description: o.description ?? '',
                confirmText: o.confirmText ?? 'Ya, Lanjutkan',
                cancelText: o.cancelText ?? 'Batal',
                showButton: true,
                onConfirm: o.onConfirm ?? null,
                onCancel: o.onCancel ?? null,
            });
        },

        /**
         * Konfirmasi Hapus Data — spesifik untuk aksi delete (merah + trash icon)
         * @example
         * AppPopup.delete({
         *   title: 'Hapus data ini?',
         *   description: 'Data yang dihapus tidak dapat dikembalikan.',
         *   confirmText: 'Ya, Hapus',
         *   onConfirm: () => form.submit(),
         * });
         */
        delete(o = {}) {
            show({
                type: 'delete',
                title: o.title ?? 'Hapus Data?',
                description: o.description ?? 'Tindakan ini tidak dapat dibatalkan. Apakah Anda yakin ingin menghapus data ini?',
                confirmText: o.confirmText ?? 'Ya, Hapus',
                cancelText: o.cancelText ?? 'Batal',
                showButton: true,
                onConfirm: o.onConfirm ?? null,
                onCancel: o.onCancel ?? null,
            });
        },

        /** Alias untuk delete */
        confirmDelete(o = {}) {
            this.delete(o);
        },

        /**
         * Konfirmasi Peringatan/Risiko Tinggi (amber)
         */
        confirmWarning(o = {}) {
            show({
                type: 'warning',
                title: o.title ?? 'Perhatian',
                description: o.description ?? '',
                confirmText: o.confirmText ?? 'Lanjutkan',
                cancelText: o.cancelText ?? 'Batal',
                showButton: true,
                onConfirm: o.onConfirm ?? null,
                onCancel: o.onCancel ?? null,
            });
        },

        /**
         * Konfirmasi Logout / Keluar Sesi (orange)
         */
        logout(o = {}) {
            show({
                type: 'logout',
                title: o.title ?? 'Keluar dari Sesi?',
                description: o.description ?? 'Anda akan keluar dari sesi admin dan harus masuk kembali.',
                confirmText: o.confirmText ?? 'Keluar',
                cancelText: o.cancelText ?? 'Batal',
                showButton: true,
                onConfirm: o.onConfirm ?? null,
                onCancel: o.onCancel ?? null,
            });
        },

        /** Tutup popup secara programatik */
        close: _close,
    };

})();
</script>
@endonce