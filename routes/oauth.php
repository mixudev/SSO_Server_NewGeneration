<?php

use App\Http\Controllers\OAuth\AuthorizationController;
use App\Http\Controllers\OAuth\RevocationController;
use App\Http\Middleware\CaptureAuthorizationCodeContext;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;

Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
    ->middleware(['throttle:oauth-token', CaptureAuthorizationCodeContext::class])
    ->name('passport.token');

Route::middleware(['auth', 'throttle:oauth-authorize'])
    ->get('/oauth/authorize', [AuthorizationController::class, 'authorize'])
    ->name('oauth.authorize');

Route::post('/oauth/revoke', RevocationController::class)
    ->middleware('throttle:oauth-revoke')
    ->name('oauth.revoke');
