@props([
    'lines' => 1,       // 1, 2, 3
    'max' => 'max-w-md', // max-w-xs (20rem), max-w-sm (24rem), max-w-md (28rem), max-w-lg (32rem)
    'title' => null,    // text for native tooltip on hover
])

@php
    $lineClamps = [
        1 => 'line-clamp-1 truncate',
        2 => 'line-clamp-2',
        3 => 'line-clamp-3',
    ];
    $clampClass = $lineClamps[$lines] ?? 'line-clamp-1';
@endphp

<div {{ $attributes->merge(['class' => "$clampClass $max text-[var(--dash-text)]", 'title' => $title]) }}>
    {{ $slot }}
</div>