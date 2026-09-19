@props([
    'title' => null,
    'kicker' => null,
    'description' => null,
    'padding' => true,
])

<section {{ $attributes->merge(['class' => 'flex flex-col rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] text-[var(--dash-text)] transition-colors']) }}>
    @if($title || $kicker || isset($header) || isset($headerActions))
        <header class="flex items-start justify-between gap-4 border-b border-[var(--dash-border)] px-6 py-4">
            <div class="space-y-0.5">
                @if($kicker)
                    <span class="font-[var(--font-ppneuemontrealmono)] text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--dash-primary)]">
                        {{ $kicker }}
                    </span>
                @endif
                @if($title)
                    <h2 class="text-base font-semibold text-[var(--dash-text-heading)]">
                        {{ $title }}
                    </h2>
                @endif
                @if($description)
                    <p class="text-xs text-[var(--dash-text-muted)]">
                        {{ $description }}
                    </p>
                @endif
                @if(isset($header))
                    {{ $header }}
                @endif
            </div>
            @if(isset($headerActions))
                <div class="flex items-center gap-2">
                    {{ $headerActions }}
                </div>
            @endif
        </header>
    @endif

    <div @class([
        'flex-1',
        'p-6' => $padding,
    ])>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <footer class="border-t border-[var(--dash-border)] bg-[var(--dash-card-hover)] px-6 py-3.5">
            {{ $footer }}
        </footer>
    @endif
</section>
