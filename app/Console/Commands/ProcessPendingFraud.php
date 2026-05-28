<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFraudDetection;
use App\Models\Transaction;
use Illuminate\Console\Command;

class ProcessPendingFraud extends Command
{
    protected $signature   = 'fraud:process-pending {--limit=50 : Max transactions to process}';
    protected $description = 'Dispatch fraud detection jobs for unscored pending transactions';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $transactions = Transaction::where('risk_score', 0)
            ->whereIn('status', ['pending', 'processing'])
            ->where('is_flagged', false)
            ->latest()
            ->limit($limit)
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No pending transactions to process.');
            return self::SUCCESS;
        }

        foreach ($transactions as $transaction) {
            ProcessFraudDetection::dispatch($transaction);
        }

        $this->info("Dispatched fraud detection for {$transactions->count()} transaction(s).");
        return self::SUCCESS;
    }
}
