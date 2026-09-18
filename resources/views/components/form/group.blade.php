@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'help' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if($label)
        <label @if($name) for="{{ $name }}" @endif class="mb-1.5 block font-[var(--font-ppneuemontrealmono)] text-[11px] font-medium uppercase tracking-[0.02em] text-[var(--dash-muted)]">
            {{ $label }}
            @if($required)
                <span class="text-[var(--dash-danger)]">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($help && (!$name || !(isset($errors) && $errors->has($name))))
        <p class="text-xs text-[var(--dash-muted)]">{{ $help }}</p>
    @endif

    @if($name)
        @error($name)
            <p class="font-[var(--font-ppneuemontrealmono)] text-xs text-[var(--dash-danger)]">{{ $message }}</p>
        @enderror
    @endif
</div>