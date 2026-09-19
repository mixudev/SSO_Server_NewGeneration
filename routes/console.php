<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('security-defense:prune')->dailyAt('02:00');
Schedule::command('audit:prune')->dailyAt('02:15');
Schedule::command('backup:clean')->dailyAt('02:30');
Schedule::command('backup:run')->dailyAt('03:00');
Schedule::command('backup:monitor')->dailyAt('03:30');
