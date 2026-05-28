<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Jobs\ProcessFraudDetection;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFraudAlertNotification implements ShouldQueue
{
    public string $queue = 'fraud';

    public function handle(TransactionCreated $event): void
    {
        // Dispatch async fraud detection for every new transaction
        ProcessFraudDetection::dispatch($event->transaction);
    }
}
