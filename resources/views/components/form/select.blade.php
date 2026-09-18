@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'help' => null,
])
@php
    $id = $attributes->get('id', $name);
    $hasError = isset($errors) && $errors->has($name);
    $describedBy = $hasError ? $id . '-error' : ($help ? $id . '-help' : null);
@endphp
<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium uppercase tracking-[0.02em] text-[var(--dash-muted)]">
            {{ $label }} @if($required)<span class="text-[var(--dash-danger)]" aria-hidden="true">*</span>@endif
        </label>
    @endif
    <select id="{{ $id }}" name="{{ $name }}" @if($required) required @endif @if($hasError) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif {{ $attributes->except('id')->merge(['class' => 'w-full rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] px-3 py-2 text-xs text-[var(--dash-text)] focus:border-[var(--dash-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--dash-primary-ring)]']) }}>
        {{ $slot }}
    </select>
    @if($help && !$hasError)<p id="{{ $id }}-help" class="text-xs text-[var(--dash-muted)]">{{ $help }}</p>@endif
    @error($name)<p id="{{ $id }}-error" class="text-xs text-[var(--dash-danger)]">{{ $message }}</p>@enderror
</div>