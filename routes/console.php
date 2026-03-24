<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notify:auctions-starting')->everyMinute();
Schedule::command('notify:auctions-ending-soon')->everyMinute();
Schedule::command('auctions:finalize')->everyMinute();
