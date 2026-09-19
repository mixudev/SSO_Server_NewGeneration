<?php

use App\Http\Controllers\Portal\ApplicationPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web'])
    ->get('/portal', [ApplicationPortalController::class, 'index'])
    ->name('sso.portal');
