@props([
    'title',
    'kicker' => null,
    'description' => null,
    'backUrl' => null,
    'backLabel' => 'Back',
])

<div {{ $attributes->merge(['class' => 'flex flex-col justify-between gap-4 md:flex-row md:items-end']) }}>
    <div class="space-y-1">
        @if($backUrl)
            <div class="mb-2">
                <x-form.button href="{{ $backUrl }}" variant="ghost" size="sm" icon="arrow-left">
                    {{ $backLabel }}
                </x-form.button>
            </div>
        @endif

        @if($kicker)
            <p class="font-[var(--font-ppneuemontrealmono)] text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--dash-primary)]">
                {{ $kicker }}
            </p>
        @endif

        <h1 class="text-2xl font-semibold tracking-[-0.01em] text-[var(--dash-text-heading)]">
            {{ $title }}
        </h1>

        @if($description)
            <p class="text-sm text-[var(--dash-text-muted)]">
                {{ $description }}
            </p>
        @endif
    </div>

    @if(isset($actions) && trim($actions) !== '')
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
