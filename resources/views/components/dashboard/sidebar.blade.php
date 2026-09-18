<aside class="dashboard-sidebar" :class="{ 'is-open': mobileOpen }" aria-label="Primary navigation">
    <!-- Sidebar Header -->
    <div class="dashboard-sidebar-header">
        <a href="{{ url('/') }}" class="dashboard-brand" aria-label="{{ config('app.name') }}">
            <span class="dashboard-brand-mark">
                <i class="bi bi-shield-check" aria-hidden="true"></i>
            </span>
            <div class="dashboard-brand-copy">
                <strong>{{ config('app.name', 'SSO Platform') }}</strong>
                <small>Identity Control Plane</small>
            </div>
        </a>

        <!-- Desktop Collapse Button -->
        <button type="button" class="dashboard-collapse-button" @click="toggleSidebar()"
            :aria-label="sidebarExpanded ? 'Collapse sidebar' : 'Expand sidebar'" title="Toggle Sidebar">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="dashboard-sidebar-nav">
        @if (isset($slot) && trim($slot) !== '')
            {{ $slot }}
        @else
            <div class="dashboard-nav-label">Core Platform</div>

            <div class="dashboard-nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="dashboard-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <i class="bi bi-graph-up" aria-hidden="true"></i>
                    <span>Dashboard</span>
                    <span class="dashboard-nav-tooltip">Dashboard Overview</span>
                </a>
            </div>

            <div class="dashboard-nav-label">Identity &amp; Access</div>

            @can('applications.view')
                <div class="dashboard-nav-item">
                    <a href="{{ route('admin.applications.index') }}"
                        class="dashboard-nav-link {{ request()->routeIs('admin.applications*') ? 'is-active' : '' }}">
                        <i class="bi bi-key" aria-hidden="true"></i>
                        <span>OAuth Clients</span>
                        <span class="dashboard-nav-tooltip">Client Applications</span>
                    </a>
                </div>
            @endcan

            @can('users.view')
                <div class="dashboard-nav-item">
                    <a href="{{ route('admin.users.index') }}"
                        class="dashboard-nav-link {{ request()->routeIs('admin.users*') ? 'is-active' : '' }}">
                        <i class="bi bi-people" aria-hidden="true"></i>
                        <span>Users &amp; Roles</span>
                        <span class="dashboard-nav-tooltip">Users &amp; Roles</span>
                    </a>
                </div>
            @endcan

            @can('organizations.view')
                <div class="dashboard-nav-item">
                    <a href="{{ route('admin.organizations.index') }}"
                        class="dashboard-nav-link {{ request()->routeIs('admin.organizations*') ? 'is-active' : '' }}">
                        <i class="bi bi-building" aria-hidden="true"></i>
                        <span>Organizations</span>
                        <span class="dashboard-nav-tooltip">Organizations</span>
                    </a>
                </div>
            @endcan

            @can('scopes.view')
                <div class="dashboard-nav-item">
                    <a href="{{ route('admin.scopes.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.scopes*') ? 'is-active' : '' }}">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        <span>Scopes</span>
                        <span class="dashboard-nav-tooltip">Scopes</span>
                    </a>
                </div>
            @endcan
            @can('claims.view')
                <div class="dashboard-nav-item">
                    <a href="{{ route('admin.claims.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.claims*') ? 'is-active' : '' }}">
                        <i class="bi bi-list-check" aria-hidden="true"></i>
                        <span>Claims</span>
                        <span class="dashboard-nav-tooltip">Claims</span>
                    </a>
                </div>
            @endcan

            <div class="dashboard-nav-label">System &amp; Security</div>

            <div class="dashboard-nav-item">
                <span class="dashboard-nav-link is-disabled" aria-disabled="true" title="Coming soon">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Security Audit</span>
                    <span class="dashboard-nav-tooltip">Coming soon</span>
                </span>
            </div>

            <div class="dashboard-nav-item">
                <span class="dashboard-nav-link is-disabled" aria-disabled="true" title="Coming soon">
                    <i class="bi bi-gear" aria-hidden="true"></i>
                    <span>Settings</span>
                    <span class="dashboard-nav-tooltip">Coming soon</span>
                </span>
            </div>
        @endif
    </nav>

</aside>
