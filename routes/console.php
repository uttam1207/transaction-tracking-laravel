<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks (Cron Scheduler)
|--------------------------------------------------------------------------
| Add this single cron entry to your server:
|   * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
*/

// Send check-in reminders at 9 AM on weekdays
Schedule::command('attendance:send-reminders --type=checkin')
    ->weekdays()
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Send check-out reminders at 6 PM on weekdays
Schedule::command('attendance:send-reminders --type=checkout')
    ->weekdays()
    ->dailyAt('18:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Generate monthly reports on the 1st of every month at 2 AM
Schedule::command('reports:generate-monthly --type=all')
    ->monthlyOn(1, '02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Clean up logs older than 90 days — runs every Sunday at 3 AM
Schedule::command('logs:cleanup --days=90')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Process any unscored pending transactions every 15 minutes
Schedule::command('fraud:process-pending --limit=50')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Check for high-risk transactions every 30 minutes
Schedule::command('fraud:check-high-risk --threshold=80')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
