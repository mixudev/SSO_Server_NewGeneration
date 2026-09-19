@props([
    'action' => '',
    'method' => 'GET',
])

<form
    method="{{ $method }}"
    @if($action) action="{{ $action }}" @endif
    {{ $attributes->merge(['class' => 'rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] p-4 shadow-none']) }}
>
    <div class="flex flex-col gap-3 md:flex-row md:items-end">
        {{ $slot }}
    </div>
</form>
