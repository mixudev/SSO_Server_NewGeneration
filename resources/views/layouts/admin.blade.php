<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Mixu SSO') }}</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body>
    <div class="page">
        @include('partials.admin.navbar')
        @include('partials.admin.sidebar')
        <div class="page-wrapper">
            @include('partials.admin.breadcrumb')
            @include('partials.shared.flash-messages')
            @yield('content')
            @include('partials.admin.footer')
        </div>
    </div>
</body>
</html>
