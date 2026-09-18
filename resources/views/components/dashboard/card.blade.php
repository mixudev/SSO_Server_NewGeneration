@props([
    'padding' => true,
    'title' => null,
    'kicker' => null,
])

<article {{ $attributes->merge(['class' => 'security-card']) }}>
    @if($title || $kicker || isset($header))
        <div class="dashboard-card-heading" style="padding: 20px 24px 0 24px;">
            <div>
                @if($kicker)
                    <span class="dashboard-stat-kicker">{{ $kicker }}</span>
                @endif
                @if($title)
                    <h3>{{ $title }}</h3>
                @endif
            </div>
            @if(isset($header))
                {{ $header }}
            @endif
        </div>
    @endif

    @if($padding)
        <div class="security-card-body">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif

    @if(isset($footer))
        <div style="padding: 14px 24px; border-top: 1px solid var(--dash-border); background-color: var(--dash-panel-raised);">
            {{ $footer }}
        </div>
    @endif
</article>
