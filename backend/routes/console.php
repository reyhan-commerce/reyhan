<?php

use Illuminate\Support\Facades\Schedule;

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

/*
|--------------------------------------------------------------------------
| Abandoned Cart Recovery Schedule
|--------------------------------------------------------------------------
| Periodically scan for abandoned carts (2+ hours inactive) and send recovery
| reminder SMS, without overlapping executions.
*/
Schedule::command('cart:recover-abandoned')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
