@props([
    'align' => 'left', // left, center, right
    'nowrap' => true,  // default true, set :nowrap="false" to allow normal wrapping
    'max' => null,     // max-w-xs, max-w-sm, max-w-md, max-w-lg
])

@php
    $alignments = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];

    $classes = [
        'px-4 py-3.5 align-middle text-[var(--dash-text)]',
        $alignments[$align] ?? 'text-left',
        $nowrap ? 'whitespace-nowrap' : 'whitespace-normal',
        $max ? $max : '',
    ];
@endphp

<td {{ $attributes->merge(['class' => trim(implode(' ', array_filter($classes)))]) }}>
    {{ $slot }}
</td>