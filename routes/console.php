<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('activitylog:clean --force')->daily()->withoutOverlapping()->onOneServer();
Schedule::command('audit:prune-exports --force')->daily()->withoutOverlapping()->onOneServer();
