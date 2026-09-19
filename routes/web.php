<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

require base_path('routes/admin.php');
require base_path('routes/portal.php');
require base_path('routes/consent.php');
require base_path('routes/oauth.php');
require base_path('routes/oidc.php');
require base_path('routes/saml.php');
require base_path('routes/api.php');
require base_path('routes/health.php');
