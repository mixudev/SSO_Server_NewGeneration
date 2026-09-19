<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Application portal — {{ config('app.name') }}</title>
    @vite('resources/css/portal.css')
</head>
<body class="portal-page">
    <main class="portal-shell">
        <nav class="portal-nav" aria-label="Portal navigation">
            <div class="portal-brand">
                <span class="portal-brand-mark" aria-hidden="true">M</span>
                <span>{{ config('app.name') }}</span>
            </div>
            <div class="portal-user">
                <span>Signed in as <strong>{{ auth()->user()->name }}</strong></span>
                <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
                    @csrf
                    <button class="portal-logout" type="submit">Sign out</button>
                </form>
            </div>
        </nav>

        <section class="portal-hero" aria-labelledby="portal-title">
            <div>
                <p class="portal-eyebrow">Your workspace</p>
                <h1 id="portal-title" class="portal-title">Everything you need,<br>in one place.</h1>
                <p class="portal-subtitle">Choose an application assigned to your account. Your access is managed securely by your organization.</p>
            </div>
            <p class="portal-stat"><strong>{{ $applications->count() }}</strong> authorized {{ Str::plural('application', $applications->count()) }} available for your account.</p>
        </section>

        @if ($applications->isEmpty())
            <section class="portal-empty" aria-live="polite">
                <div class="portal-app-mark" style="margin: 0 auto;">—</div>
                <h2>No applications are available yet</h2>
                <p>Contact your organization administrator if you believe you should have access.</p>
            </section>
        @else
            <section class="portal-grid" aria-label="Available applications">
                @foreach ($applications as $application)
                    <article class="portal-card">
                        <div>
                            <div class="portal-card-top">
                                <div class="portal-app-mark" aria-hidden="true">{{ Str::upper(Str::substr($application->name, 0, 1)) }}</div>
                                <span class="portal-status">Available</span>
                            </div>
                            <p class="portal-organization">{{ $application->organization->name }}</p>
                            <h2>{{ $application->name }}</h2>
                            <p>{{ $application->description ?: 'Secure application access for your organization.' }}</p>
                        </div>
                        <a class="portal-open" href="{{ $application->homepage_url }}" target="_blank" rel="noopener noreferrer">
                            <span>Open application</span>
                            <span aria-hidden="true">↗</span>
                        </a>
                    </article>
                @endforeach
            </section>
        @endif
    </main>
</body>
</html>
