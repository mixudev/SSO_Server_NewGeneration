@props([
    'align' => 'left', // left, center, right
])

@php
    $alignments = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];
@endphp

<th {{ $attributes->merge(['class' => 'px-4 py-3 font-[var(--font-ppneuemontrealmono)] text-[10px] font-medium uppercase tracking-[0.02em] ' . ($alignments[$align] ?? 'text-left')]) }}>
    <x-table.label>{{ $slot }}</x-table.label>
</th>