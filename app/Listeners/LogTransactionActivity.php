<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogTransactionActivity implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(TransactionCreated $event): void
    {
        $tx = $event->transaction;

        AuditLog::create([
            'user_id'    => $tx->user_id,
            'action'     => 'transaction_created',
            'model_type' => 'Transaction',
            'model_id'   => $tx->id,
            'description'=> "Transaction {$tx->transaction_id} created — {$tx->currency} {$tx->amount} ({$tx->status})",
            'ip_address' => request()->ip(),
            'new_values' => json_encode($tx->only([
                'transaction_id', 'amount', 'currency', 'type', 'status', 'risk_score',
            ])),
        ]);
    }
}
