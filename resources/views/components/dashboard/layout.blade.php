@props([
    'title' => 'Dashboard',
    'breadcrumb' => 'Dashboard',
    'userName' => 'Administrator',
    'userEmail' => 'Authenticated operator',
    'logoutUrl' => null,
])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>

    <!-- Zero-Flicker Bootstrapper (Theme + Collapsed Sidebar) -->
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('dashboard.theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (!theme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                if (localStorage.getItem('dashboard.sidebar') === 'collapsed') {
                    document.documentElement.classList.add('sidebar-is-collapsed');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])
</head>
<body class="dashboard-body" x-data="dashboardShell">
    <div class="dashboard-viewport" :class="{ 'sidebar-collapsed': !sidebarExpanded }">
        <!-- Mobile Drawer Backdrop -->
        <div
            x-cloak
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="dashboard-mobile-overlay"
            @click="closeMobile()"
            aria-hidden="true"
        ></div>

        <!-- Sidebar -->
        <x-dashboard.sidebar>
            {{ $navigation ?? '' }}
        </x-dashboard.sidebar>

        <!-- Main Shell -->
        <div class="dashboard-shell">
            <x-dashboard.header
                :user-name="$userName"
                :user-email="$userEmail"
                :logout-url="$logoutUrl"
                :breadcrumb="$breadcrumb"
            >
                <x-slot:headerActions>
                    {{ $headerActions ?? '' }}
                </x-slot:headerActions>
            </x-dashboard.header>

            <main id="main-content" class="dashboard-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Alert / Popup Notification System -->
    <x-allert />
</body>
</html>
