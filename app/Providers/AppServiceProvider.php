<?php

namespace App\Providers;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Domain\OAuth\Contracts\OAuthClientRepositoryInterface;
use App\Infrastructure\Identity\LocalRsaKeyManager;
use App\Infrastructure\Identity\SecurityAuditLogger;
use App\Infrastructure\OAuth\PassportOAuthClientRepository;
use App\Infrastructure\Passport\ActiveClientRepository;
use App\Infrastructure\Passport\OidcBearerTokenResponse;
use App\Infrastructure\Passport\OidcTokenContext;
use App\Listeners\AuthenticationSecuritySubscriber;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Passport::ignoreRoutes();

        $this->app->singleton(KeyManagerInterface::class, LocalRsaKeyManager::class);
        $this->app->singleton(OidcTokenContext::class);
        $this->app->singleton(AuditLoggerInterface::class, SecurityAuditLogger::class);
        $this->app->singleton(OAuthClientRepositoryInterface::class, PassportOAuthClientRepository::class);
        $this->app->singleton(ClientRepository::class, ActiveClientRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('oidc-public', static fn (Request $request): Limit => Limit::perMinute(60)->by($request->ip()));
        RateLimiter::for('oauth-authorize', static fn (Request $request): Limit => Limit::perMinute(30)->by($request->user()?->getAuthIdentifier() ?: $request->ip()));
        RateLimiter::for('oauth-revoke', static fn (Request $request): Limit => Limit::perMinute(30)->by($request->user()?->getAuthIdentifier() ?: $request->ip()));
        RateLimiter::for('oauth-token', static fn (Request $request): Limit => Limit::perMinute(60)->by($request->input('client_id', $request->ip())));

        Passport::useAuthorizationServerResponseType(new OidcBearerTokenResponse);

        Passport::tokensCan([
            'openid' => 'OpenID Connect identity',
            'profile' => 'Basic profile',
            'email' => 'Email address',
        ]);

        Event::subscribe(AuthenticationSecuritySubscriber::class);
    }
}
