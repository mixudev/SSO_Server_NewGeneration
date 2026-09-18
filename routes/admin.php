<?php

use App\Http\Controllers\Admin\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:admin.dashboard.view'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::view('/', 'pages.admin.dashboard.index')->name('dashboard');
        Route::get('/applications', [ApplicationController::class, 'index'])
            ->middleware('can:applications.view')
            ->name('applications.index');
    });
