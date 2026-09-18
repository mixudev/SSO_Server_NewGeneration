<?php

use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\ClaimController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ScopeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:admin.dashboard.view'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::view('/', 'pages.admin.dashboard.index')->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'show'])
            ->name('profile.show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::post('/profile/avatar', [ProfileController::class, 'avatarUpdate'])
            ->middleware('throttle:10,1')
            ->name('profile.avatar.update');
        Route::delete('/profile/avatar', [ProfileController::class, 'avatarDestroy'])
            ->middleware('throttle:10,1')
            ->name('profile.avatar.destroy');
        Route::post('/profile/password', [ProfileController::class, 'passwordUpdate'])
            ->middleware('throttle:5,1')
            ->name('profile.password.update');
        Route::get('/profile/security', [ProfileController::class, 'security'])
            ->name('profile.security');
        Route::get('/profile/security/two-factor', [ProfileController::class, 'twoFactorSetup'])
            ->name('profile.security.two-factor');
        Route::post('/profile/security/two-factor', [ProfileController::class, 'twoFactorConfirm'])
            ->middleware('throttle:5,1')
            ->name('profile.security.two-factor.confirm');
        Route::get('/profile/security/password-reset', [ProfileController::class, 'passwordReset'])
            ->name('profile.security.password-reset');
        Route::post('/profile/security/password-reset', [ProfileController::class, 'passwordResetSend'])
            ->middleware('throttle:5,1')
            ->name('profile.security.password-reset.send');
        Route::post('/profile/security/passkeys/options', [ProfileController::class, 'passkeyOptions'])
            ->middleware('throttle:10,1')
            ->name('profile.security.passkeys.options');
        Route::post('/profile/security/passkeys', [ProfileController::class, 'passkeyRegister'])
            ->middleware('throttle:5,1')
            ->name('profile.security.passkeys.store');
        Route::delete('/profile/security/passkeys/{id}', [ProfileController::class, 'passkeyDestroy'])
            ->middleware('throttle:5,1')
            ->name('profile.security.passkeys.destroy');
        Route::delete('/profile/security/sessions/{id}', [ProfileController::class, 'sessionDestroy'])
            ->middleware('throttle:10,1')
            ->name('profile.security.sessions.destroy');
        Route::post('/profile/security/sessions/revoke-others', [ProfileController::class, 'sessionsDestroyOthers'])
            ->middleware('throttle:5,1')
            ->name('profile.security.sessions.destroy-others');
        Route::put('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');
        Route::get('/users', [UserController::class, 'index'])
            ->middleware('can:users.view')
            ->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])
            ->middleware('can:users.view')
            ->name('users.show');
        Route::get('/organizations', [OrganizationController::class, 'index'])
            ->middleware('can:organizations.view')
            ->name('organizations.index');
        Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])
            ->middleware('can:organizations.view')
            ->name('organizations.show');
        Route::get('/scopes', [ScopeController::class, 'index'])
            ->middleware('can:scopes.view')
            ->name('scopes.index');
        Route::post('/scopes', [ScopeController::class, 'store'])
            ->middleware('can:scopes.manage')
            ->name('scopes.store');
        Route::put('/scopes/{scope}', [ScopeController::class, 'update'])
            ->middleware('can:scopes.manage')
            ->name('scopes.update');
        Route::get('/claims', [ClaimController::class, 'index'])
            ->middleware('can:claims.view')
            ->name('claims.index');
        Route::post('/claims', [ClaimController::class, 'store'])
            ->middleware('can:claims.manage')
            ->name('claims.store');
        Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])
            ->middleware('can:organizations.manage')
            ->name('organizations.update');
        Route::put('/users/{user}', [UserController::class, 'update'])
            ->middleware('can:users.manage')
            ->name('users.update');
        Route::get('/applications', [ApplicationController::class, 'index'])
            ->middleware('can:applications.view')
            ->name('applications.index');
        Route::get('/applications/create', [ApplicationController::class, 'wizardBasic'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.basic');
        Route::post('/applications/create/basic', [ApplicationController::class, 'wizardBasicStore'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.basic.store');
        Route::get('/applications/create/protocol', [ApplicationController::class, 'wizardProtocol'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.protocol');
        Route::post('/applications/create/protocol', [ApplicationController::class, 'wizardProtocolStore'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.protocol.store');
        Route::get('/applications/create/redirect', [ApplicationController::class, 'wizardRedirect'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.redirect');
        Route::post('/applications/create/redirect', [ApplicationController::class, 'wizardRedirectStore'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.redirect.store');
        Route::get('/applications/create/scopes', [ApplicationController::class, 'wizardScopes'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.scopes');
        Route::post('/applications/create/scopes', [ApplicationController::class, 'wizardScopesStore'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.scopes.store');
        Route::get('/applications/create/claims', [ApplicationController::class, 'wizardClaims'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.claims');
        Route::post('/applications/create/claims', [ApplicationController::class, 'wizardClaimsStore'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.claims.store');
        Route::get('/applications/create/security', [ApplicationController::class, 'wizardSecurity'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.security');
        Route::post('/applications/create/security', [ApplicationController::class, 'wizardSecurityStore'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.security.store');
        Route::get('/applications/create/review', [ApplicationController::class, 'wizardReview'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.review');
        Route::post('/applications/create/complete', [ApplicationController::class, 'wizardComplete'])
            ->middleware('can:applications.create')
            ->name('applications.wizard.complete');
        Route::get('/applications/{application}', [ApplicationController::class, 'show'])
            ->middleware('can:applications.view')
            ->name('applications.show');
        Route::post('/applications', [ApplicationController::class, 'store'])
            ->middleware('can:applications.create')
            ->name('applications.store');
        Route::put('/applications/{application}', [ApplicationController::class, 'update'])
            ->middleware('can:applications.update')
            ->name('applications.update');
        Route::delete('/applications/{application}', [ApplicationController::class, 'destroy'])
            ->middleware('can:applications.delete')
            ->name('applications.destroy');
    });
