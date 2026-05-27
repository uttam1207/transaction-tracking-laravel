<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Transaction $transaction)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('transactions')];
    }

    public function broadcastAs(): string
    {
        return 'transaction.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->transaction->id,
            'transaction_id' => $this->transaction->transaction_id,
            'amount' => $this->transaction->amount,
            'currency' => $this->transaction->currency,
            'status' => $this->transaction->status,
            'is_flagged' => $this->transaction->is_flagged,
            'risk_score' => $this->transaction->risk_score,
            'created_at' => $this->transaction->created_at->toISOString(),
        ];
    }
}
