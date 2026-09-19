<?php

use App\Http\Controllers\Oidc\DiscoveryController;
use App\Http\Controllers\Oidc\EndSessionController;
use App\Http\Controllers\Oidc\JwksController;
use App\Http\Controllers\Oidc\UserInfoController;
use Illuminate\Support\Facades\Route;

Route::get('/.well-known/openid-configuration', DiscoveryController::class)
    ->middleware('throttle:oidc-public')
    ->name('oidc.discovery');
Route::get('/.well-known/jwks.json', JwksController::class)
    ->middleware('throttle:oidc-public')
    ->name('oidc.jwks');
Route::get('/oauth/end-session', EndSessionController::class)
    ->middleware(['auth', 'throttle:oidc-public'])
    ->name('oidc.end-session');
Route::middleware(['auth:api', 'throttle:oidc-public'])->get('/oauth/userinfo', UserInfoController::class)
    ->name('oidc.userinfo');
