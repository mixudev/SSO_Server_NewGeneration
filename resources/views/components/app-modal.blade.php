{{--
|--------------------------------------------------------------------------
| AppModal Component — Full Tailwind CSS Edition
|--------------------------------------------------------------------------
| Simpan di: resources/views/components/app-modal.blade.php
| Include di sembarang tempat, tapi sebaiknya di bawah halaman.
|
| -----------------------------------------------------------------------
| CARA PAKAI:
| -----------------------------------------------------------------------
| <x-app-modal id="myModal" maxWidth="md" title="Modal Title" description="Teks pembantu" icon="<svg>...</svg>" iconColor="indigo">
|     <p>Ini adalah konten modal.</p>
|     <x-slot name="footer">
|         <button onclick="AppModal.close('myModal')" class="modal-btn-cancel">Tutup</button>
|         <button onclick="simpan()" class="modal-btn-primary">Simpan</button>
|     </x-slot>
| </x-app-modal>
|
| Buka via JS    : AppModal.open('myModal')
| Tutup via JS   : AppModal.close('myModal')
|
| Slot yang Tersedia:
| - header (opsional, menggantikan seluruh blok header default)
| - default slot (isi dalam body)
| - footer (opsional, untuk tombol aksi)
--}}
@props([
    'id',
    'maxWidth' => 'md',
    'title' => null,
    'description' => null,
    'icon' => null,
    'iconColor' => 'indigo',
])

@php
    $maxWidthClass = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        'full' => 'max-w-full m-4',
    ][$maxWidth] ?? 'max-w-md';

    $iconMap = [
        'indigo'  => ['bg' => 'bg-[var(--dash-primary-soft)]', 'text' => 'text-[var(--dash-primary)]', 'ring' => 'ring-[var(--dash-primary-ring)]'],
        'violet'  => ['bg' => 'bg-[var(--dash-primary-soft)]', 'text' => 'text-[var(--dash-primary)]', 'ring' => 'ring-[var(--dash-primary-ring)]'],
        'red'     => ['bg' => 'bg-red-100 dark:bg-red-500/10', 'text' => 'text-red-600 dark:text-red-400', 'ring' => 'ring-red-100 dark:ring-red-500/20'],
        'emerald' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/10', 'text' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-500/20'],
        'amber'   => ['bg' => 'bg-amber-100 dark:bg-amber-500/10', 'text' => 'text-amber-600 dark:text-amber-400', 'ring' => 'ring-amber-100 dark:ring-amber-500/20'],
    ];
    $c = $iconMap[$iconColor] ?? $iconMap['indigo'];

    $renderedIcon = null;
    if ($icon) {
        if (str_contains($icon, '<') || str_contains($icon, '</')) {
            $renderedIcon = $icon;
        } else {
            $iconClass = str_starts_with($icon, 'bi-') ? $icon : 'bi-' . $icon;
            $renderedIcon = '<i class="bi ' . e($iconClass) . ' text-[18px]"></i>';
        }
    }
@endphp

@once
<style>
    @keyframes appModalOverlayIn  { from { opacity: 0 } to { opacity: 1 } }
    @keyframes appModalOverlayOut { from { opacity: 1 } to { opacity: 0 } }
    @keyframes appModalBoxIn      { from { opacity: 0; transform: scale(.85) translateY(20px) } to { opacity: 1; transform: scale(1) translateY(0) } }
    @keyframes appModalBoxOut     { from { opacity: 1; transform: scale(1) translateY(0) } to { opacity: 0; transform: scale(.88) translateY(12px) } }
    @keyframes appModalIconPop    { from { opacity: 0; transform: scale(.5) rotate(-10deg) } to { opacity: 1; transform: scale(1) rotate(0deg) } }
    @keyframes appModalRingPulse  { 0% { transform: scale(1); opacity: .6 } 100% { transform: scale(1.4); opacity: 0 } }

    .modal-anim-overlay-in  { animation: appModalOverlayIn  .25s ease both }
    .modal-anim-overlay-out { animation: appModalOverlayOut .25s ease both }
    .modal-anim-box-in      { animation: appModalBoxIn  .4s cubic-bezier(.34,1.4,.64,1) both }
    .modal-anim-box-out     { animation: appModalBoxOut .28s ease both }
    .modal-anim-icon        { animation: appModalIconPop .5s cubic-bezier(.34,1.5,.64,1) both; animation-delay: .12s }
    .modal-anim-ring        { animation: appModalRingPulse 2s ease-out infinite; animation-delay: .6s; pointer-events: none; }

    .modal-close-x { transition: background .15s, color .15s, transform .2s ease }
    .modal-close-x:hover { transform: rotate(90deg) }

    /* --- Form Input Styling Standar (Khusus di dalam Modal) --- */
    .modal-body input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
    .modal-body select,
    .modal-body textarea {
        width: 100%;
        padding: 0.625rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
        font-family: inherit;
    }
    .dark .modal-body input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
    .dark .modal-body select,
    .dark .modal-body textarea {
        background-color: #18181f; /* matching dark theme */
        border-color: rgba(255, 255, 255, 0.08);
        color: #e2e8f0;
    }
    .modal-body input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):focus,
    .modal-body select:focus,
    .modal-body textarea:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }
    .modal-body input::placeholder, .modal-body textarea::placeholder { color: #94a3b8; }

    /* File Input Styling */
    .modal-body input[type="file"] { width: 100%; font-size: 0.875rem; color: #64748b; cursor: pointer; }
    .dark .modal-body input[type="file"] { color: #94a3b8; }
    .modal-body input[type="file"]::file-selector-button {
        margin-right: 1rem; padding: 0.5rem 1rem; border-radius: 0; border: none;
        font-size: 0.75rem; font-weight: 600; background-color: #eef2ff; color: #4f46e5;
        cursor: pointer; transition: all 0.2s;
    }
    .modal-body input[type="file"]::file-selector-button:hover { background-color: #e0e7ff; }
    .dark .modal-body input[type="file"]::file-selector-button { background-color: rgba(99, 102, 241, 0.1); color: #818cf8; }
    .dark .modal-body input[type="file"]::file-selector-button:hover { background-color: rgba(99, 102, 241, 0.2); }

    /* Label Styling */
    .modal-body label {
        display: block; font-size: 0.6875rem; font-weight: 600; color: #64748b;
        text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem;
    }
    .dark .modal-body label { color: var(--dash-muted); }

    .app-modal .modal-box {
        border-radius: var(--dash-radius);
        background: var(--dash-card);
        border-color: var(--dash-border);
        color: var(--dash-text);
    }
    .app-modal .modal-header,
    .app-modal .modal-footer {
        border-color: var(--dash-border);
    }
    .app-modal .modal-footer { background: var(--dash-card); }
    .app-modal .modal-body label { color: var(--dash-muted); }
    .app-modal .modal-body select,
    .app-modal .modal-body input,
    .app-modal .modal-body textarea {
        border-color: var(--dash-border);
        background: var(--dash-card);
        color: var(--dash-text);
        border-radius: var(--dash-radius);
    }
    .app-modal .modal-body select:focus,
    .app-modal .modal-body input:focus,
    .app-modal .modal-body textarea:focus {
        border-color: var(--dash-primary);
        box-shadow: 0 0 0 2px var(--dash-primary-ring);
    }
    .app-modal .modal-close-x:hover { background: var(--dash-primary-soft); color: var(--dash-primary); }
    .app-modal .modal-btn-primary { background: var(--dash-action); border-color: var(--dash-action); }
    .app-modal .modal-btn-primary:hover { background: var(--dash-primary-hover); }
    .app-modal .modal-btn-cancel { background: var(--dash-card); border-color: var(--dash-border); color: var(--dash-text); }
    .app-modal .modal-btn-cancel:hover { background: var(--dash-primary-soft); }
    .app-modal .modal-btn-danger { background: var(--dash-danger); border-color: var(--dash-danger); }

    /* --- Elegant Action Buttons (Firm/Tegas, No Glow) --- */
    .modal-btn-primary {
        padding: 0.625rem 1.25rem; font-size: 0.75rem; font-weight: 700; border-radius: 0;
        background-color: #4f46e5; color: #ffffff; border: 1px solid #4338ca; cursor: pointer; transition: all 0.2s;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
    }
    .modal-btn-primary:hover { background-color: #4338ca; }
    .modal-btn-primary:active { transform: scale(0.98); }

    .modal-btn-danger {
        padding: 0.625rem 1.25rem; font-size: 0.75rem; font-weight: 700; border-radius: 0;
        background-color: #dc2626; color: #ffffff; border: 1px solid #b91c1c; cursor: pointer; transition: all 0.2s;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
    }
    .modal-btn-danger:hover { background-color: #b91c1c; }
    .modal-btn-danger:active { transform: scale(0.98); }

    .modal-btn-cancel {
        padding: 0.625rem 1.25rem; font-size: 0.75rem; font-weight: 700; border-radius: 0;
        background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
    }
    .modal-btn-cancel:hover { background-color: #e2e8f0; }
    .modal-btn-cancel:active { transform: scale(0.98); }
    .dark .app-modal .modal-btn-primary {
        background-color: var(--dash-primary);
        border-color: var(--dash-primary);
        color: var(--dash-action-text);
    }
    .dark .app-modal .modal-btn-primary:hover { background-color: var(--dash-primary-hover); }
    .dark .modal-btn-cancel { background-color: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: #e2e8f0; }
    .dark .modal-btn-cancel:hover { background-color: rgba(255, 255, 255, 0.1); }


</style>
@endonce

<div
    id="{{ $id }}"
    class="app-modal fixed inset-0 z-[9900] hidden items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    @if($description) aria-describedby="{{ $id }}-description" @endif
    data-modal-id="{{ $id }}"
>
    <!-- Simple Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer"></div>

    <!-- Modal Card -->
    <div class="modal-box relative w-full {{ $maxWidthClass }} mx-auto overflow-hidden bg-white border border-black/[0.06] shadow-[0_0_0_1px_rgba(0,0,0,0.03),0_20px_60px_-10px_rgba(0,0,0,0.18),0_8px_24px_-4px_rgba(0,0,0,0.08)] dark:bg-[#18181f] dark:border-white/[0.07] dark:shadow-[0_0_0_1px_rgba(255,255,255,0.04),0_20px_60px_-10px_rgba(0,0,0,0.7),0_8px_24px_-4px_rgba(0,0,0,0.4)] flex flex-col max-h-[90vh] rounded-none">

        <!-- Header -->
        <div class="modal-header px-6 py-5 border-b border-gray-100 dark:border-white/[0.08] flex items-center justify-between shrink-0">
            @if(isset($header))
                {{ $header }}
            @else
                <div class="flex items-center gap-6">
                    @if($renderedIcon)
                    <div class="modal-anim-icon relative w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $c['bg'] }} ring-4 {{ $c['ring'] }} {{ $c['text'] }}">
                        <div class="modal-anim-ring absolute inset-[-4px] rounded-full border-2 {{ str_replace('text-', 'border-', $c['text']) }}"></div>
                        <div class="relative z-10 flex items-center justify-center">
                            {!! $renderedIcon !!}
                        </div>
                    </div>
                    @endif
                    <div class="min-w-0 flex flex-col justify-center">
                        <h3 id="{{ $id }}-title" class="text-[17px] font-bold text-gray-900 dark:text-slate-100 tracking-tight leading-tight">
                                                    {{ $title ?? 'Modal Title' }}
                                                </h3>
                        @if($description)
                        <p id="{{ $id }}-description" class="max-w-[260px] truncate text-[12px] text-gray-500 dark:text-slate-400 mt-1 leading-relaxed" title="{{ $description }}">
                            {{ $description }}
                        </p>
                        @endif
                    </div>
                </div>
            @endif

            <button type="button" aria-label="Tutup" onclick="AppModal.close('{{ $id }}')" class="modal-close-x w-8 h-8 flex items-center justify-center rounded-none bg-transparent border-0 cursor-pointer text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-600 dark:hover:bg-white/[0.08] dark:hover:text-gray-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-300">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="modal-body p-6 overflow-y-auto custom-scrollbar">
            {{ $slot }}
        </div>

        <!-- Footer -->
        @if(isset($footer))
        <div class="modal-footer px-6 py-5 bg-gray-50/80 dark:bg-black/20 border-t border-gray-100 dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>

@once
<script>
    window.AppModal = {
        open(id) {
            const modal = document.getElementById(id);
            if (!modal) return console.error(`Modal with id "${id}" not found.`);

            modal._previousFocus = document.activeElement;
            const backdrop = modal.querySelector('.modal-backdrop');
            const firstFocusable = modal.querySelector('button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href]');
            setTimeout(() => firstFocusable?.focus(), 0);
            const box = modal.querySelector('.modal-box');

            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            void modal.offsetWidth; // force reflow

            document.body.classList.add('overflow-hidden');

            backdrop.classList.remove('modal-anim-overlay-out');
            box.classList.remove('modal-anim-box-out');
            backdrop.classList.add('modal-anim-overlay-in');
            box.classList.add('modal-anim-box-in');
        },

        close(id) {
            const modal = document.getElementById(id);
            if (!modal) return;

            const backdrop = modal.querySelector('.modal-backdrop');
            const box = modal.querySelector('.modal-box');

            backdrop.classList.replace('modal-anim-overlay-in', 'modal-anim-overlay-out');
            box.classList.replace('modal-anim-box-in', 'modal-anim-box-out');

            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                backdrop.classList.remove('modal-anim-overlay-out');
                box.classList.remove('modal-anim-box-out');
                const previousFocus = modal._previousFocus;
                previousFocus?.focus();
                delete modal._previousFocus;
            }, 280);
        }
    };

    // Close on backdrop click
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-backdrop')) {
            const modalId = e.target.closest('.app-modal').id;
            window.AppModal.close(modalId);
        }
    });

    // Close on Escape and keep keyboard focus inside the active modal
    document.addEventListener('keydown', (e) => {
        const openModals = Array.from(document.querySelectorAll('.app-modal:not(.hidden)')).filter(m => m.style.display === 'flex');
        const activeModal = openModals[openModals.length - 1];
        if (activeModal && e.key === 'Tab') {
            const focusable = Array.from(activeModal.querySelectorAll('button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href]'));
            if (focusable.length) {
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        }
        if (e.key === 'Escape') {
            // Find top-most open modal (excluding AppPopup)
            const openModals = Array.from(document.querySelectorAll('.app-modal:not(.hidden)')).filter(m => m.style.display === 'flex');
            if (openModals.length > 0) {
                window.AppModal.close(openModals[openModals.length - 1].id);
            }
        }
    });
</script>
@endonce
