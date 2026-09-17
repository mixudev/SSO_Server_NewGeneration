<aside class="navbar navbar-vertical navbar-expand-lg" id="admin-sidebar" aria-label="Primary navigation">
    <div class="container-fluid">
        <h2 class="navbar-brand d-none d-lg-block">Administration</h2>
        <div class="collapse navbar-collapse show" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                        <span class="nav-link-title">Overview</span>
                    </a>
                </li>
                @can('applications.view')
                    <li class="nav-item"><span class="nav-link disabled">Applications</span></li>
                @endcan
                @can('users.view')
                    <li class="nav-item"><span class="nav-link disabled">Users</span></li>
                @endcan
                @can('audit.view')
                    <li class="nav-item"><span class="nav-link disabled">Security</span></li>
                @endcan
            </ul>
        </div>
    </div>
</aside>
