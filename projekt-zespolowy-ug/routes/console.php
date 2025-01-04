<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


app()->singleton(Schedule::class, function () {
    return new Schedule();
});

$schedule = app(Schedule::class);

$schedule->command('reminders:check')->everyMinute();
