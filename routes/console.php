<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// No scheduled pruning of activity_log: it is the audit / recovery trail for
// deleted and changed records, so nothing deletes it automatically.
