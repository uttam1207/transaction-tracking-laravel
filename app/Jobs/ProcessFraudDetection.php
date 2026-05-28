<?php

namespace App\Jobs;

use App\Models\FraudAlert;
use App\Models\Transaction;
use App\Services\FraudDetectionService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFraudDetection implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public Transaction $transaction) {}

    public function handle(FraudDetectionService $fraudService, NotificationService $notificationService): void
    {
        $result = $fraudService->analyze($this->transaction);

        $this->transaction->update([
            'risk_score' => $result['risk_score'],
            'is_flagged' => $result['is_flagged'],
            'fraud_reason' => implode(', ', array_column($result['triggered_rules'], 'name')),
        ]);

        if ($result['is_flagged']) {
            $notificationService->sendFraudAlert($this->transaction, $result);
        }

        if ($result['recommendation'] === 'block') {
            $this->transaction->update(['status' => 'blocked']);
        }
    }
}
