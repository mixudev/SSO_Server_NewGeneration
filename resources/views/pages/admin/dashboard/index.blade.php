<x-dashboard.layout
    title="Overview"
    breadcrumb="Dashboard"
    :user-name="auth()->user()->name"
    :user-email="auth()->user()->email"
    :logout-url="Route::has('logout') ? route('logout') : url('/logout')"
>
    <x-slot:headerActions>

    </x-slot:headerActions>

    <!-- Hero Banner with Quick Actions -->
    @include('pages.admin.dashboard.partials.hero')

    <!-- Platform Domain Stat Cards -->
    @include('pages.admin.dashboard.partials.stats')

    <!-- Posture Checklist and Operator Queue -->
    @include('pages.admin.dashboard.partials.posture')

    <!-- Modals & Action Handlers -->
    @include('pages.admin.dashboard.partials.modals')
</x-dashboard.layout>
