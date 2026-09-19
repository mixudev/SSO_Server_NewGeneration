<?php

use App\Http\Controllers\OAuth\ConsentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web'])
    ->post('/oauth/consent/{transaction}/approve', [ConsentController::class, 'approve'])
    ->name('oauth.consent.approve');
