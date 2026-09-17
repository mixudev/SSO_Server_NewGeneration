<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:admin.dashboard.view'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::view('/', 'pages.admin.dashboard.index')->name('dashboard');
    });
