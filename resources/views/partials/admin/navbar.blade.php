<header class="navbar navbar-expand-md d-print-none" aria-label="Application header">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand navbar-brand-autodark" href="{{ route('admin.dashboard') }}">{{ config('app.name', 'Mixu SSO') }}</a>
        <div class="navbar-nav flex-row order-md-last">
            @auth
                <span class="nav-link">{{ auth()->user()->name }}</span>
            @endauth
        </div>
    </div>
</header>
