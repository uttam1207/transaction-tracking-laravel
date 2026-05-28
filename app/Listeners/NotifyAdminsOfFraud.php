<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminsOfFraud implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(private NotificationService $notificationService) {}

    public function handle(TransactionCreated $event): void
    {
        $tx = $event->transaction;

        // Only alert if the transaction is already flagged at creation
        if (!$tx->is_flagged || $tx->risk_score < 70) {
            return;
        }

        $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'admin']))->get();

        foreach ($admins as $admin) {
            $this->notificationService->send(
                $admin,
                'Flagged Transaction Detected',
                "Transaction {$tx->transaction_id} was flagged with risk score {$tx->risk_score}.",
                'danger',
                ['transaction_id' => $tx->id],
                "/admin/transactions/{$tx->id}"
            );
        }
    }
}
