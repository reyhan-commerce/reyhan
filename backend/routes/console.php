<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Backup Schedules
|--------------------------------------------------------------------------
| Clean obsolete backups daily, run daily database snapshot,
| and run full backup (code/files + DB) weekly.
*/
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run --only-db')->daily()->at('01:30');
Schedule::command('backup:run')->weeklyOn(5, '02:00');
