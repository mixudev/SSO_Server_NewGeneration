<div class="container-xl pt-3">
    <ol class="breadcrumb" aria-label="Breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Overview</a></li>
        @if (! request()->routeIs('admin.dashboard'))
            <li class="breadcrumb-item active" aria-current="page">{{ $title ?? 'Page' }}</li>
        @endif
    </ol>
</div>
