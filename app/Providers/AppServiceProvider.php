<?php

namespace App\Providers;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Infrastructure\Identity\LocalRsaKeyManager;
use App\Infrastructure\Identity\SecurityAuditLogger;
use App\Listeners\AuthenticationSecuritySubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(KeyManagerInterface::class, LocalRsaKeyManager::class);
        $this->app->singleton(AuditLoggerInterface::class, SecurityAuditLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::subscribe(AuthenticationSecuritySubscriber::class);
    }
}
