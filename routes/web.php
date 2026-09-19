<?php

use App\Http\Controllers\AuthenticatedLandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('auth')->get('/app', AuthenticatedLandingController::class)->name('authenticated.landing');

Route::middleware('auth')->get('/home', AuthenticatedLandingController::class);

require base_path('routes/admin.php');
require base_path('routes/portal.php');
require base_path('routes/consent.php');
require base_path('routes/oauth.php');
require base_path('routes/oidc.php');
require base_path('routes/saml.php');
require base_path('routes/api.php');
require base_path('routes/health.php');
