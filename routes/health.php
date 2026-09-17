<?php

use Illuminate\Support\Facades\Route;

Route::get('/health/live', static fn (): array => ['status' => 'ok'])
    ->name('health.liveness');
