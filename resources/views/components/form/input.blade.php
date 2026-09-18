@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'help' => null,
])

@php
    $hasError = isset($errors) && $errors->has($name);
    $inputValue = old($name, $value);

    $baseClasses = 'w-full rounded-[var(--dash-radius)] border bg-[var(--dash-card)] px-3 py-2 text-xs text-[var(--dash-text)] placeholder:text-[var(--dash-muted-light)] transition-colors focus:outline-none focus:ring-2 disabled:cursor-not-allowed';
    $borderClasses = $hasError
        ? 'border-[var(--dash-danger)] focus:border-[var(--dash-danger)] focus:ring-[var(--dash-danger-soft)]'
        : 'border-[var(--dash-border)] focus:border-[var(--dash-primary)] focus:ring-[var(--dash-primary-ring)]';
    $labelClasses = 'mb-1.5 block font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium uppercase tracking-[0.02em] text-[var(--dash-muted)]';
    $helpClasses = 'mt-1 text-xs text-[var(--dash-muted)]';
    $errorClasses = 'mt-1 font-[var(--font-ppneuemontrealmono)] text-xs text-[var(--dash-danger)]';
@endphp

<div>
    @if($label)
        <label for="{{ $attributes->get('id', $name) }}" class="{{ $labelClasses }}">
            {{ $label }}
            @if($required)
                <span class="text-[var(--dash-danger)]">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $attributes->get('id', $name) }}"
        value="{{ $inputValue }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => $baseClasses . ' ' . $borderClasses . ($disabled ? ' opacity-60 cursor-not-allowed' : '')]) }}
    />

    @if($help && !$hasError)
        <p class="{{ $helpClasses }}">{{ $help }}</p>
    @endif

    @error($name)
        <p class="{{ $errorClasses }}">{{ $message }}</p>
    @enderror
</div>