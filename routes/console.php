<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clean activity logs older than 12 months — runs on the 1st of each month at midnight
Schedule::command('activitylog:clean --days=365')
    ->monthly()
    ->description('Prune activity log entries older than 12 months');
