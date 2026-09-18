@props([
    'userName'   => 'Administrator',
    'userRole'   => 'Administrator',
    'userEmail'  => 'Authenticated operator',
    'logoutUrl'  => null,
    'breadcrumb' => 'Dashboard',
])

<header class="dashboard-header" x-data="dashboardSearch">
    <div class="dashboard-header-leading">
        <!-- Mobile Hamburger -->
        <button
            type="button"
            class="dashboard-mobile-button"
            @click="openMobile()"
            aria-label="Open navigation"
        >
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>

        <!-- Breadcrumb -->
        <nav class="dashboard-breadcrumb" aria-label="Breadcrumbs">
            <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard Root">
                <i class="bi bi-house" aria-hidden="true"></i>
            </a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>{{ $breadcrumb }}</strong>
        </nav>

        <!-- Search Trigger -->
        <div class="dashboard-quick-search">
            <button
                type="button"
                class="dashboard-search-trigger"
                @click="open()"
                aria-label="Open command palette"
                id="search-palette-trigger"
            >
                <i class="bi bi-search" aria-hidden="true"></i>
                <span>Search or jump to...</span>
                <kbd>⌘ K</kbd>
            </button>
        </div>
    </div>

    <!-- Header Actions -->
    <div class="dashboard-header-actions">
        {{ $headerActions ?? '' }}

        <!-- Theme Toggle -->
        <button
            type="button"
            class="dashboard-header-btn"
            @click="toggleTheme()"
            :aria-label="isDark ? 'Switch to light' : 'Switch to dark'"
            :title="isDark ? 'Light Mode' : 'Dark Mode'"
            id="theme-toggle-btn"
        >
            <i x-show="isDark" x-cloak class="bi bi-sun" aria-hidden="true"></i>
            <i x-show="!isDark" class="bi bi-moon" aria-hidden="true"></i>
        </button>

        <!-- Notification Bell -->
        <button
            type="button"
            class="dashboard-header-btn"
            aria-label="System notifications"
            id="notification-btn"
            onclick="AppPopup.info({ title: 'System Notifications', description: 'All SSO and OIDC services running normally. Average latency: 24ms.' })"
        >
            <i class="bi bi-bell" aria-hidden="true"></i>
            <span class="dashboard-notification-dot" aria-hidden="true"></span>
        </button>

        <!-- ======================================================
             Profile Dropdown
             ====================================================== -->
        <div class="dash-profile" x-data="{ profileOpen: false }">
            <button
                type="button"
                class="dash-profile-trigger"
                @click="profileOpen = !profileOpen"
                :aria-expanded="profileOpen"
                id="profile-menu-btn"
            >
                <span class="dash-avatar-sm">{{ strtoupper(substr($userName, 0, 1)) }}</span>
                <span class="dash-profile-trigger-name">{{ $userName }}</span>
                <i class="bi bi-chevron-down dash-profile-trigger-chevron"
                   :class="{ 'rotated': profileOpen }"></i>
            </button>

            <!-- Dropdown Panel -->
            <div
                x-cloak
                x-show="profileOpen"
                @click.outside="profileOpen = false"
                x-transition:enter="dp-enter"
                x-transition:enter-start="dp-enter-from"
                x-transition:enter-end="dp-enter-to"
                x-transition:leave="dp-leave"
                x-transition:leave-start="dp-leave-from"
                x-transition:leave-end="dp-leave-to"
                class="dash-profile-dropdown"
                role="menu"
            >
                <!-- Identity Card -->
                <div class="dp-identity">
                    <span class="dp-avatar">{{ strtoupper(substr($userName, 0, 1)) }}</span>
                    <div class="dp-identity-info">
                        <p class="dp-identity-name">{{ $userName }}</p>
                        <span class="dp-identity-role">
                            <i class="bi bi-shield-check"></i>
                            {{ $userRole }}
                        </span>
                    </div>
                </div>

                <!-- Menu Items -->
                <nav class="dp-nav" role="group">
                    <a href="{{ route('admin.profile.show') }}" class="dp-item" role="menuitem" @click="profileOpen = false">
                        <span class="dp-item-icon"><i class="bi bi-person"></i></span>
                        <span class="dp-item-label">My Profile</span>
                        <i class="bi bi-arrow-right dp-item-arrow"></i>
                    </a>
                    <a href="{{ route('admin.profile.edit') }}" class="dp-item" role="menuitem" @click="profileOpen = false">
                        <span class="dp-item-icon"><i class="bi bi-sliders"></i></span>
                        <span class="dp-item-label">Profile Settings</span>
                        <i class="bi bi-arrow-right dp-item-arrow"></i>
                    </a>
                    <span class="dp-item is-disabled" role="menuitem" aria-disabled="true">
                        <span class="dp-item-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="dp-item-label">Activity Log</span>
                        <span class="text-[10px] font-semibold uppercase tracking-wide">Soon</span>
                    </span>
                </nav>

                <!-- Sign Out -->
                <div class="dp-footer">
                    @if($logoutUrl)
                        <form x-ref="logoutForm" method="POST" action="{{ $logoutUrl }}">@csrf
                            <button
                                type="button"
                                class="dp-signout"
                                role="menuitem"
                                @click="
                                    profileOpen = false;
                                    window.confirmAction({
                                        type: 'logout',
                                        title: 'Confirm Sign Out',
                                        description: 'You will be signed out of the admin session and must re-authenticate.',
                                        confirmText: 'Sign Out',
                                        cancelText: 'Stay',
                                        onConfirm: () => $refs.logoutForm.submit()
                                    });
                                "
                            >
                                <i class="bi bi-box-arrow-right"></i>
                                Sign Out
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         Command Palette / Spotlight Search
         ============================================================ -->
    <div
        x-cloak
        x-show="openState"
        x-transition:enter="sp-overlay-enter"
        x-transition:enter-start="sp-overlay-from"
        x-transition:enter-end="sp-overlay-to"
        x-transition:leave="sp-overlay-leave"
        x-transition:leave-start="sp-overlay-from-leave"
        x-transition:leave-end="sp-overlay-to-leave"
        class="sp-overlay"
        @keydown.escape.window="close()"
        @click="if ($event.target === $el) close()"
        role="dialog"
        aria-modal="true"
        aria-label="Command palette"
    >
        <div
            class="sp-panel"
            @click.stop
            x-transition:enter="sp-panel-enter"
            x-transition:enter-start="sp-panel-from"
            x-transition:enter-end="sp-panel-to"
            x-transition:leave="sp-panel-leave"
            x-transition:leave-start="sp-panel-from-leave"
            x-transition:leave-end="sp-panel-to-leave"
        >
            <!-- Search Input Row -->
            <div class="sp-input-row">
                <div class="sp-input-icon">
                    <i class="bi bi-search" aria-hidden="true"></i>
                </div>
                <input
                    x-ref="searchInput"
                    x-model="query"
                    type="search"
                    placeholder="Search pages, actions, settings..."
                    aria-label="Command palette search"
                    id="command-palette-input"
                    autocomplete="off"
                    spellcheck="false"
                >
                <button class="sp-close-btn" @click="close()" title="Close (ESC)">
                    <kbd>ESC</kbd>
                </button>
            </div>

            <!-- Results Body -->
            <div class="sp-body">

                <!-- Navigation group -->
                <template x-if="navItems.filter(i => !query || i.name.toLowerCase().includes(query.toLowerCase())).length > 0">
                    <div class="sp-group">
                        <p class="sp-group-label">
                            <i class="bi bi-compass"></i>
                            Navigation
                        </p>
                        <template
                            x-for="item in navItems.filter(i => !query || i.name.toLowerCase().includes(query.toLowerCase()))"
                            :key="item.name"
                        >
                            <a :href="item.href" class="sp-item" @click="close()">
                                <span class="sp-item-icon"><i :class="item.icon"></i></span>
                                <span class="sp-item-label" x-text="item.name"></span>
                                <span class="sp-item-badge sp-item-badge-nav">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    Go
                                </span>
                            </a>
                        </template>
                    </div>
                </template>

                <!-- Actions group -->
                <template x-if="actionItems.filter(a => !query || a.name.toLowerCase().includes(query.toLowerCase())).length > 0">
                    <div class="sp-group">
                        <p class="sp-group-label">
                            <i class="bi bi-lightning-charge"></i>
                            Quick Actions
                        </p>
                        <template
                            x-for="act in actionItems.filter(a => !query || a.name.toLowerCase().includes(query.toLowerCase()))"
                            :key="act.name"
                        >
                            <button class="sp-item sp-item-action" @click="close(); act.action()">
                                <span class="sp-item-icon sp-item-icon-action"><i :class="act.icon"></i></span>
                                <span class="sp-item-label" x-text="act.name"></span>
                                <span class="sp-item-badge sp-item-badge-action">
                                    <i class="bi bi-play"></i>
                                    Run
                                </span>
                            </button>
                        </template>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="
                    navItems.filter(i => !query || i.name.toLowerCase().includes(query.toLowerCase())).length === 0 &&
                    actionItems.filter(a => !query || a.name.toLowerCase().includes(query.toLowerCase())).length === 0
                ">
                    <div class="sp-empty">
                        <span class="sp-empty-icon"><i class="bi bi-emoji-neutral"></i></span>
                        <p>No results for <strong x-text="'&quot;' + query + '&quot;'"></strong></p>
                        <small>Try a different keyword or browse the navigation.</small>
                    </div>
                </template>
            </div>

            <!-- Footer Hints -->
            <div class="sp-footer">
                <div class="sp-footer-hints">
                    <span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span>
                    <span><kbd>↵</kbd> Select</span>
                    <span><kbd>ESC</kbd> Close</span>
                </div>
                <span class="sp-footer-brand">
                    <i class="bi bi-shield-check"></i>
                    SSO Control Plane
                </span>
            </div>
        </div>
    </div>
</header>
