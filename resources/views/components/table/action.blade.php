@props([
    'variant' => 'secondary',
    'icon' => null,
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'border-[var(--dash-action)] bg-[var(--dash-action)] text-[var(--dash-action-text)] hover:bg-[var(--dash-primary-hover)]',
        'secondary' => 'border-[var(--dash-border)] bg-[var(--dash-card)] text-[var(--dash-text-heading)] hover:border-[var(--dash-primary)] hover:text-[var(--dash-primary)]',
        'danger' => 'border-[var(--dash-danger)] bg-[var(--dash-danger-soft)] text-[var(--dash-danger)] hover:brightness-95',
        'outline' => 'border-[var(--dash-border)] bg-transparent text-[var(--dash-muted)] hover:border-[var(--dash-primary)] hover:text-[var(--dash-primary)]',
    ];

    $classes = 'inline-flex h-8 w-8 items-center justify-center rounded-[var(--dash-radius)] border font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium transition-colors focus-visible:outline-2 focus-visible:outline-[var(--dash-primary)] focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50 ' . ($variants[$variant] ?? $variants['secondary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'aria-label' => $attributes->get('title')]) }}>
        @if ($icon)
            <i class="bi {{ str_starts_with($icon, 'bi-') ? $icon : 'bi-' . $icon }} text-[11px]" aria-hidden="true"></i>
        @endif
        <span class="sr-only">{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes, 'aria-label' => $attributes->get('title')]) }}>
        @if ($icon)
            <i class="bi {{ str_starts_with($icon, 'bi-') ? $icon : 'bi-' . $icon }} text-[11px]" aria-hidden="true"></i>
        @endif
        <span class="sr-only">{{ $slot }}</span>
    </button>
@endif