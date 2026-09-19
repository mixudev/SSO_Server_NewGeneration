@props([
    'name',
    'value' => '1',
    'checked' => false,
    'label' => null,
    'description' => null,
    'disabled' => false,
    'required' => false,
])

@php
    $inputId = $attributes->get('id', $name . '-' . Str::slug($value));
    $hasError = isset($errors) && $errors->has($name);
    $isChecked = old($name) !== null ? (is_array(old($name)) ? in_array($value, old($name)) : old($name) == $value) : $checked;
@endphp

<label for="{{ $inputId }}" class="flex items-start gap-3 cursor-pointer select-none group">
    <div class="flex items-center h-5">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ $value }}"
            @checked($isChecked)
            @if($disabled) disabled @endif
            @if($required) required @endif
            {{ $attributes->merge(['class' => 'h-4 w-4 rounded-[var(--dash-radius)] border-[var(--dash-border)] text-[var(--dash-primary)] focus:ring-[var(--dash-primary-ring)] focus:ring-offset-0 disabled:opacity-50 cursor-pointer accent-[var(--dash-primary)]']) }}
        />
    </div>

    <div class="text-xs">
        @if($label)
            <span class="font-medium text-[var(--dash-text-heading)] group-hover:text-[var(--dash-primary)] transition-colors">
                {{ $label }}
            </span>
        @endif

        @if($description)
            <p class="text-[var(--dash-text-muted)] mt-0.5 leading-normal">
                {{ $description }}
            </p>
        @endif

        {{ $slot }}
    </div>
</label>
