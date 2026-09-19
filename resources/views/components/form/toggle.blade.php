@props([
    'name',
    'value' => '1',
    'checked' => false,
    'label' => null,
    'description' => null,
    'disabled' => false,
])

@php
    $inputId = $attributes->get('id', $name);
    $isChecked = old($name, $checked);
@endphp

<div class="flex items-center justify-between gap-4">
    <div class="text-xs">
        @if($label)
            <label for="{{ $inputId }}" class="font-medium text-[var(--dash-text-heading)] cursor-pointer">
                {{ $label }}
            </label>
        @endif
        @if($description)
            <p class="text-[var(--dash-text-muted)] mt-0.5">
                {{ $description }}
            </p>
        @endif
    </div>

    <label class="relative inline-flex items-center cursor-pointer">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ $value }}"
            @checked($isChecked)
            @if($disabled) disabled @endif
            class="sr-only peer"
            {{ $attributes }}
        >
        <div class="w-9 h-5 bg-[var(--dash-border)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--dash-primary)]"></div>
    </label>
</div>
