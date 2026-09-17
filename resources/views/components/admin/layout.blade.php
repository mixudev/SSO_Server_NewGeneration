@props(['title' => null])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name', 'Mixu SSO') }}</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body>
    <div class="page">
        <x-admin.navbar />
        <x-admin.sidebar />
        <div class="page-wrapper">
            <x-admin.breadcrumb :title="$title" />
            <x-shared.flash-messages />
            {{ $slot }}
            <x-admin.footer />
        </div>
    </div>
</body>
</html>
