@props([
    'padding' => true,
    'title' => null,
    'kicker' => null,
])

<article {{ $attributes->merge(['class' => 'security-card rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] text-[var(--dash-text)] transition-colors']) }}>
    @if($title || $kicker || isset($header))
        <div class="dashboard-card-heading px-6 pt-5 pb-0 flex items-start justify-between gap-4">
            <div>
                @if($kicker)
                    <span class="dashboard-stat-kicker font-[var(--font-ppneuemontrealmono)] text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--dash-primary)] block mb-1">
                        {{ $kicker }}
                    </span>
                @endif
                @if($title)
                    <h3 class="text-base font-semibold text-[var(--dash-text-heading)] m-0">
                        {{ $title }}
                    </h3>
                @endif
            </div>
            @if(isset($header))
                {{ $header }}
            @endif
        </div>
    @endif

    @if($padding)
        <div class="security-card-body p-6">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif

    @if(isset($footer))
        <div class="px-6 py-3.5 border-t border-[var(--dash-border)] bg-[var(--dash-card-hover)]">
            {{ $footer }}
        </div>
    @endif
</article>
