@props([
    'hover' => true,
    'striped' => false,
    'fixed' => false, // Set true to enforce strict column widths
    'head' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-[var(--dash-radius)] border border-[var(--dash-border)] bg-[var(--dash-card)] shadow-sm']) }}>
    <table @class([
        'w-full border-collapse text-left text-sm text-[var(--dash-text)]',
        'table-fixed' => $fixed,
        'hover:[&>tbody>tr]:bg-[var(--dash-card-hover)]' => $hover,
        '[&>tbody>tr:nth-child(even)]:bg-[var(--dash-body)]' => $striped,
    ])>
        @if($head)
            <thead class="border-b border-[var(--dash-border)] bg-[var(--dash-primary-soft)] text-[var(--dash-text-heading)]">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-[var(--dash-border-subtle)] bg-[var(--dash-card)]">
            {{ $slot }}
        </tbody>
    </table>
</div>