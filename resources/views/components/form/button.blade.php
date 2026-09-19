@props([
    'variant' => 'primary',
    'size' => 'sm',
    'icon' => null,
    'iconRight' => null,
    'type' => 'button',
    'href' => null,
])

@php
    $variants = [
        'primary'   => 'border-[var(--dash-action)] bg-[var(--dash-action)] text-[var(--dash-action-text)] hover:border-[var(--dash-primary-hover)] hover:bg-[var(--dash-primary-hover)] hover:text-white',
        'secondary' => 'border-[var(--dash-border)] bg-[var(--dash-card)] text-[var(--dash-text-heading)] hover:border-[var(--dash-primary)] hover:text-[var(--dash-primary)]',
        'danger'    => 'border-[var(--dash-danger)] bg-[var(--dash-danger)] text-white hover:brightness-90',
        'success'   => 'border-[var(--dash-success)] bg-[var(--dash-success)] text-white hover:brightness-90',
        'warning'   => 'border-[var(--dash-warning)] bg-[var(--dash-warning)] text-white hover:brightness-90',
        'outline'   => 'border-[var(--dash-border)] bg-transparent text-[var(--dash-text-heading)] hover:border-[var(--dash-primary)] hover:text-[var(--dash-primary)]',
        'ghost'     => 'border-transparent bg-transparent text-[var(--dash-muted)] hover:bg-[var(--dash-primary-soft)] hover:text-[var(--dash-primary)]',
        'link'      => 'border-transparent bg-transparent text-[var(--dash-primary)] hover:underline p-0 inline-flex shadow-none',
        'icon'      => 'border-[var(--dash-border)] bg-[var(--dash-card)] text-[var(--dash-muted)] hover:border-[var(--dash-primary)] hover:text-[var(--dash-primary)]',
    ];

    $sizes = [
        'xs' => 'px-2 py-1 text-[10px]',
        'sm' => 'px-2.5 py-1.5 text-[11px]',
        'md' => 'px-3 py-2 text-xs',
        'lg' => 'px-4 py-2.5 text-sm',
    ];

    $isLinkVariant = $variant === 'link';
    $isIconVariant = $variant === 'icon';

    $paddingAndSize = $isLinkVariant
        ? ''
        : ($isIconVariant ? 'p-2 text-xs' : ($sizes[$size] ?? $sizes['md']));

    $classes = 'inline-flex items-center justify-center gap-1.5 rounded-[var(--dash-radius)] border font-[var(--font-ppneuemontreal)] font-medium leading-tight transition-colors focus-visible:outline-2 focus-visible:outline-[var(--dash-primary)] focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50 select-none '
        . ($variants[$variant] ?? $variants['primary']) . ' '
        . $paddingAndSize;

    $leftIconClass = $icon
        ? (str_starts_with($icon, 'bi-') ? $icon : 'bi bi-' . $icon)
        : null;

    $rightIconClass = $iconRight
        ? (str_starts_with($iconRight, 'bi-') ? $iconRight : 'bi bi-' . $iconRight)
        : null;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($leftIconClass)
            <i class="{{ $leftIconClass }}" aria-hidden="true"></i>
        @endif
        {{ $slot }}
        @if($rightIconClass)
            <i class="{{ $rightIconClass }}" aria-hidden="true"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($leftIconClass)
            <i class="{{ $leftIconClass }}" aria-hidden="true"></i>
        @endif
        {{ $slot }}
        @if($rightIconClass)
            <i class="{{ $rightIconClass }}" aria-hidden="true"></i>
        @endif
    </button>
@endif