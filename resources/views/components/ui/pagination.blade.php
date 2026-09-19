@props([
    'paginator',
])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col items-center justify-between gap-3 border-t border-[var(--dash-border)] pt-4 sm:flex-row">
        <div class="text-xs text-[var(--dash-text-muted)]">
            Showing
            <span class="font-medium text-[var(--dash-text-heading)]">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-medium text-[var(--dash-text-heading)]">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-medium text-[var(--dash-text-heading)]">{{ $paginator->total() }}</span>
            results
        </div>

        @php($paginationLinks = $paginator->linkCollection()->values())
        <div class="flex items-center gap-1">
            @foreach ($paginationLinks as $index => $link)
                @if ($index === 0)
                    @if ($link['url'])
                        <a href="{{ $link['url'] }}" rel="prev" class="pagination-button" aria-label="Previous"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
                    @else
                        <span class="pagination-button pagination-button-disabled" aria-disabled="true" aria-label="Previous"><i class="bi bi-chevron-left" aria-hidden="true"></i></span>
                    @endif
                @elseif ($index === $paginationLinks->count() - 1)
                    @if ($link['url'])
                        <a href="{{ $link['url'] }}" rel="next" class="pagination-button" aria-label="Next"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    @else
                        <span class="pagination-button pagination-button-disabled" aria-disabled="true" aria-label="Next"><i class="bi bi-chevron-right" aria-hidden="true"></i></span>
                    @endif
                @elseif ($link['active'])
                    <span class="pagination-button pagination-button-active" aria-current="page">{{ $link['label'] }}</span>
                @elseif ($link['url'])
                    <a href="{{ $link['url'] }}" class="pagination-button" aria-label="Go to page {{ $link['page'] }}">{{ $link['label'] }}</a>
                @else
                    <span class="pagination-count" aria-hidden="true">...</span>
                @endif
            @endforeach

            @if ($paginator->lastPage() > 5)
                <span class="pagination-count">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
            @endif
        </div>
    </nav>
@endif
