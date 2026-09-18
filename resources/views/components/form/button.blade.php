@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'border-[var(--dash-action)] bg-[var(--dash-action)] text-[var(--dash-action-text)] hover:border-[var(--dash-primary-hover)] hover:bg-[var(--dash-primary-hover)] hover:text-white',
        'secondary' => 'border-[var(--dash-border)] bg-[var(--dash-card)] text-[var(--dash-text-heading)] hover:border-[var(--dash-primary)] hover:text-[var(--dash-primary)]',
        'danger' => 'border-[var(--dash-danger)] bg-[var(--dash-danger)] text-white hover:brightness-90',
        'outline' => 'border-[var(--dash-border)] bg-transparent text-[var(--dash-text-heading)] hover:border-[var(--dash-primary)] hover:text-[var(--dash-primary)]',
        'ghost' => 'border-transparent bg-transparent text-[var(--dash-muted)] hover:bg-[var(--dash-primary-soft)] hover:text-[var(--dash-primary)]',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-[11px]',
        'md' => 'px-3 py-2 text-xs',
        'lg' => 'px-4 py-2.5 text-sm',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-[var(--dash-radius)] border font-[var(--font-ppneuemontreal)] font-medium leading-tight transition-colors focus-visible:outline-2 focus-visible:outline-[var(--dash-primary)] focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50 '
        . ($variants[$variant] ?? $variants['primary']) . ' '
        . ($sizes[$size] ?? $sizes['md']);

    $iconClass = $icon
        ? (str_starts_with($icon, 'bi-') ? $icon : 'bi bi-' . $icon)
        : null;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($iconClass)
            <i class="{{ $iconClass }}" aria-hidden="true"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($iconClass)
            <i class="{{ $iconClass }}" aria-hidden="true"></i>
        @endif
        {{ $slot }}
    </button>
@endif