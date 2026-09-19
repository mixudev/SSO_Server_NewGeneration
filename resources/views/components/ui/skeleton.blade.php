@props([
    'type' => 'line', // line, card, table-row, avatar
    'count' => 1,
    'width' => null,
    'height' => null,
])

@for($i = 0; $i < $count; $i++)
    @if($type === 'avatar')
        <div {{ $attributes->merge(['class' => 'h-10 w-10 animate-pulse rounded-full bg-[var(--dash-border)]']) }}></div>
    @elseif($type === 'card')
        <div {{ $attributes->merge(['class' => 'flex flex-col rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] p-6 space-y-4 animate-pulse']) }}>
            <div class="h-4 w-1/3 bg-[var(--dash-border)] rounded-[var(--dash-radius)]"></div>
            <div class="h-3 w-full bg-[var(--dash-border-subtle)] rounded-[var(--dash-radius)]"></div>
            <div class="h-3 w-2/3 bg-[var(--dash-border-subtle)] rounded-[var(--dash-radius)]"></div>
        </div>
    @elseif($type === 'table-row')
        <tr class="animate-pulse">
            <td colspan="100%" class="px-4 py-3">
                <div class="h-4 w-full bg-[var(--dash-border-subtle)] rounded-[var(--dash-radius)]"></div>
            </td>
        </tr>
    @else
        <div {{ $attributes->merge(['class' => 'animate-pulse rounded-[var(--dash-radius)] bg-[var(--dash-border-subtle)] ' . ($height ?? 'h-4') . ' ' . ($width ?? 'w-full')]) }}></div>
    @endif
@endfor
