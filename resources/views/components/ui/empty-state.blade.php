@props([
    'icon' => 'inbox',
    'title' => 'No items found',
    'description' => null,
])

@php
    $iconClass = str_starts_with($icon, 'bi-') ? $icon : 'bi-' . $icon;
@endphp

<div {{ $attributes->merge(['class' => 'rounded-[var(--dash-radius)] border border-dashed border-[var(--dash-border)] bg-[var(--dash-card)] px-6 py-12 text-center']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[var(--dash-primary-soft)] text-[var(--dash-primary)]">
        <i class="bi {{ $iconClass }} text-xl" aria-hidden="true"></i>
    </div>
    <h3 class="mt-4 text-base font-semibold text-[var(--dash-text-heading)]">
        {{ $title }}
    </h3>
    @if($description)
        <p class="mx-auto mt-1 max-w-sm text-sm text-[var(--dash-text-muted)]">
            {{ $description }}
        </p>
    @endif
    @if(isset($action) && trim($action) !== '')
        <div class="mt-5 flex items-center justify-center gap-3">
            {{ $action }}
        </div>
    @elseif(isset($slot) && trim($slot) !== '')
        <div class="mt-5 flex items-center justify-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
