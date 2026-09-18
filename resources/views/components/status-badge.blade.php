@props(['value', 'variant' => null])
@php
    $variant ??= match (strtolower((string) $value)) {
        'active', 'success', 'verified', 'available' => 'success',
        'draft', 'pending', 'warning', 'suspended' => 'warning',
        'revoked', 'retired', 'failed', 'error' => 'danger',
        default => 'neutral',
    };
    $classes = [
        'success' => 'border-[var(--dash-success)] text-[var(--dash-success)]',
        'warning' => 'border-[var(--dash-warning)] text-[var(--dash-warning)]',
        'danger' => 'border-[var(--dash-danger)] text-[var(--dash-danger)]',
        'neutral' => 'border-[var(--dash-border)] text-[var(--dash-muted)]',
    ][$variant] ?? 'border-[var(--dash-border)] text-[var(--dash-muted)]';
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center border px-2 py-1 font-[var(--font-ppneuemontrealmono)] text-[10px] font-medium uppercase tracking-[0.08em] ' . $classes]) }}>{{ $value }}</span>