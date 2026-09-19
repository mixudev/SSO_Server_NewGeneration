@props([
    'variant' => 'neutral',
    'size' => 'sm',
    'pill' => false,
    'dot' => false,
])

@php
    $variants = [
        'primary'   => 'border-[var(--dash-primary)] bg-[var(--dash-primary-soft)] text-[var(--dash-primary)]',
        'secondary' => 'border-[var(--dash-border)] bg-[var(--dash-card)] text-[var(--dash-text-heading)]',
        'neutral'   => 'border-[var(--dash-border)] bg-[var(--dash-card)] text-[var(--dash-muted)]',
        'success'   => 'border-[var(--dash-success)] bg-[var(--dash-success-soft)] text-[var(--dash-success)]',
        'warning'   => 'border-[var(--dash-warning)] bg-[var(--dash-warning-soft)] text-[var(--dash-warning)]',
        'danger'    => 'border-[var(--dash-danger)] bg-[var(--dash-danger-soft)] text-[var(--dash-danger)]',
        'info'      => 'border-[var(--dash-primary)] bg-[var(--dash-primary-soft)] text-[var(--dash-primary)]',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[10px]',
        'md' => 'px-2.5 py-1 text-[11px]',
    ];

    $classes = 'inline-flex items-center border font-[var(--font-ppneuemontrealmono)] font-medium uppercase tracking-[0.06em] '
        . ($pill ? 'rounded-full' : 'rounded-[var(--dash-radius)]') . ' '
        . ($variants[$variant] ?? $variants['neutral']) . ' '
        . ($sizes[$size] ?? $sizes['sm']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="mr-1.5 inline-block h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
