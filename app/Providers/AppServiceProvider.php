<?php

namespace App\Providers;

use App\Events\TransactionCreated;
use App\Listeners\LogTransactionActivity;
use App\Listeners\NotifyAdminsOfFraud;
use App\Listeners\SendFraudAlertNotification;
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
    }
}
