@props([
    'action' => null,
    'method' => 'GET',
    'title' => 'Filters',
    'description' => null,
    'resetUrl' => null,
    'modalTitle' => null,
    'buttonLabel' => 'Filter',
    'searchName' => null,
    'searchValue' => null,
    'searchPlaceholder' => 'Search...',
    'searchLabel' => 'Search',
])

@php
    $componentId = $attributes->get('id', 'filter-section');
    $formAction = $action ?: url()->current();
    $formId = $componentId.'-form';
    $modalId = $componentId.'-modal';
@endphp

<section {{ $attributes->merge(['class' => 'flex items-center justify-between gap-3']) }} aria-label="{{ $title }}">
    <div class="flex min-w-0 flex-1 items-center gap-3">
        <p class="hidden shrink-0 text-xs font-semibold uppercase tracking-[0.1em] text-[var(--dash-text-muted)] sm:block">{{ $title }}</p>
        @if($searchName)
            <div class="relative min-w-0 max-w-md flex-1">
                <label for="{{ $componentId }}-search" class="sr-only">{{ $searchLabel }}</label>
                <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[var(--dash-muted)]" aria-hidden="true"></i>
                <input id="{{ $componentId }}-search" name="{{ $searchName }}" value="{{ old($searchName, $searchValue) }}" placeholder="{{ $searchPlaceholder }}" form="{{ $formId }}" class="w-full border border-[var(--dash-border)] bg-[var(--dash-card)] py-2 pl-8 pr-3 text-xs text-[var(--dash-text)] outline-none transition-colors placeholder:text-[var(--dash-muted-light)] focus:border-[var(--dash-primary)] focus:ring-2 focus:ring-[var(--dash-primary-ring)]" />
            </div>
        @else
            <p class="truncate text-xs text-[var(--dash-text-muted)] sm:hidden" title="{{ $description ?: $title }}">{{ $title }}</p>
        @endif
    </div>
    <div class="flex shrink-0 items-center gap-2">
        @if($resetUrl)
            <x-form.button href="{{ $resetUrl }}" variant="ghost" size="sm">Reset</x-form.button>
        @endif
        <x-form.button type="button" variant="secondary" size="sm" icon="sliders" onclick="AppModal.open('{{ $modalId }}')">{{ $buttonLabel }}</x-form.button>
    </div>
</section>

<x-app-modal id="{{ $modalId }}" maxWidth="lg" :title="$modalTitle ?: $title" :description="$description" icon="sliders">
    <form id="{{ $formId }}" method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" action="{{ $formAction }}" class="grid gap-4 sm:grid-cols-2">
        @if(strtoupper($method) !== 'GET')
            @csrf
            @method($method)
        @endif
        {{ $slot }}
    </form>
    <x-slot name="footer">
        <x-form.button type="button" variant="ghost" onclick="AppModal.close('{{ $modalId }}')">Cancel</x-form.button>
        <x-form.button type="submit" form="{{ $formId }}">Apply filters</x-form.button>
    </x-slot>
</x-app-modal>
