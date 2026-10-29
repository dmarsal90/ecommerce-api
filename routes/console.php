<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Unpaid card orders hold stock; release it (needs `php artisan schedule:run` every minute in production).
Schedule::command('orders:expire-unpaid')->everyTenMinutes()->withoutOverlapping();
