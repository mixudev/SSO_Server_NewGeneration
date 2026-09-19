<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health/live', static fn (): array => ['status' => 'ok'])
    ->name('health.liveness');
Route::get('/health/ready', [HealthController::class, 'ready'])
    ->name('health.readiness');
