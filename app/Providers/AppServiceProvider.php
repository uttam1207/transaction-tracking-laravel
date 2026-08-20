<?php

namespace App\Providers;

use App\Events\TransactionCreated;
use App\Listeners\LogTransactionActivity;
use App\Listeners\NotifyAdminsOfFraud;
use App\Listeners\SendFraudAlertNotification;
use App\Models\Setting;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Share fraud detection status to all transaction views so badges can be
        // hidden when the engine is disabled — data is preserved in the DB.
        View::composer('admin.transactions.*', function ($view) {
            $view->with('fraudEnabled', Setting::get('fraud_detection_enabled', '1') !== '0');
        });

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
