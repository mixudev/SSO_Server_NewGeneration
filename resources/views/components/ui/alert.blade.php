@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $configs = [
        'info' => [
            'classes' => 'border-[var(--dash-primary)] bg-[var(--dash-primary-soft)] text-[var(--dash-text)]',
            'icon'    => 'bi-info-circle',
            'iconColor' => 'text-[var(--dash-primary)]',
        ],
        'success' => [
            'classes' => 'border-[var(--dash-success)] bg-[var(--dash-success-soft)] text-[var(--dash-text)]',
            'icon'    => 'bi-check-circle',
            'iconColor' => 'text-[var(--dash-success)]',
        ],
        'warning' => [
            'classes' => 'border-[var(--dash-warning)] bg-[var(--dash-warning-soft)] text-[var(--dash-text)]',
            'icon'    => 'bi-exclamation-triangle',
            'iconColor' => 'text-[var(--dash-warning)]',
        ],
        'danger' => [
            'classes' => 'border-[var(--dash-danger)] bg-[var(--dash-danger-soft)] text-[var(--dash-text)]',
            'icon'    => 'bi-x-circle',
            'iconColor' => 'text-[var(--dash-danger)]',
        ],
    ];

    $cfg = $configs[$variant] ?? $configs['info'];
@endphp

<div
    {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-[var(--dash-radius)] border p-4 text-xs leading-relaxed ' . $cfg['classes']]) }}
    role="alert"
    @if($dismissible) x-data="{ open: true }" x-show="open" @endif
>
    <i class="bi {{ $cfg['icon'] }} mt-0.5 text-base shrink-0 {{ $cfg['iconColor'] }}" aria-hidden="true"></i>

    <div class="flex-1">
        @if($title)
            <h4 class="font-semibold text-[var(--dash-text-heading)] mb-0.5">{{ $title }}</h4>
        @endif
        <div class="text-[var(--dash-text)]">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button
            type="button"
            @click="open = false"
            class="text-[var(--dash-muted)] hover:text-[var(--dash-text-heading)] transition-colors p-0.5 -mr-1 -mt-1"
            aria-label="Dismiss alert"
        >
            <i class="bi bi-x text-lg" aria-hidden="true"></i>
        </button>
    @endif
</div>
