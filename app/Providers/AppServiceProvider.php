<?php

namespace App\Providers;

use App\Events\TransactionCreated;
use App\Listeners\LogTransactionActivity;
use App\Listeners\NotifyAdminsOfFraud;
use App\Listeners\SendFraudAlertNotification;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Wire event → listeners
        Event::listen(TransactionCreated::class, SendFraudAlertNotification::class);
        Event::listen(TransactionCreated::class, NotifyAdminsOfFraud::class);
        Event::listen(TransactionCreated::class, LogTransactionActivity::class);

        // Disable MySQL 8.0.30+ Generated Invisible Primary Key (GIPK) which
        // auto-adds a hidden 'my_row_id' column to tables with no explicit PK.
        Event::listen(ConnectionEstablished::class, function ($event) {
            if ($event->connection->getDriverName() === 'mysql') {
                try {
                    $event->connection->unprepared('SET sql_generate_invisible_primary_key = OFF');
                } catch (\Throwable $e) {
                    // Ignore — older MySQL versions don't have this variable
                }
            }
        });
    }
}
