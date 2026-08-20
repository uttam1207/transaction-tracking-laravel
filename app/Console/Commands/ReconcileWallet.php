<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileWallet extends Command
{
    protected $signature   = 'wallet:reconcile {--dry-run : Preview changes without writing to DB} {--force : Skip confirmation prompt}';
    protected $description = 'Sync wallet balance with all success transactions that were never reflected in wallet_transactions';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $wallet = Wallet::company();

        $this->info('Company wallet current balance: ₹' . number_format($wallet->balance, 2));

        // Find all success transaction IDs already reflected in wallet_transactions (by reference)
        $reflected = WalletTransaction::whereNotNull('reference')
            ->pluck('reference')
            ->flip(); // flip for O(1) lookups

        // Find success transactions NOT yet reflected
        $missing = Transaction::where('status', 'success')
            ->get()
            ->filter(fn ($tx) => !isset($reflected[$tx->transaction_id]));

        if ($missing->isEmpty()) {
            $this->info('Wallet is already in sync — no missing entries found.');
            return self::SUCCESS;
        }

        $this->warn("{$missing->count()} success transaction(s) not yet reflected in wallet:");

        $netCredit = 0;
        $netDebit  = 0;

        foreach ($missing as $tx) {
            $sign = $tx->type === 'credit' ? '+' : '-';
            $this->line("  {$sign}₹" . number_format($tx->net_amount, 2) . "  [{$tx->type}]  {$tx->transaction_id}");
            if ($tx->type === 'credit') {
                $netCredit += (float) $tx->net_amount;
            } else {
                $netDebit += (float) $tx->net_amount;
            }
        }

        $expectedNewBalance = (float) $wallet->balance + $netCredit - $netDebit;

        $this->newLine();
        $this->line('Net credits to add : ₹' . number_format($netCredit, 2));
        $this->line('Net debits to add  : ₹' . number_format($netDebit, 2));
        $this->line('Expected balance after sync: ₹' . number_format($expectedNewBalance, 2));

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes written. Re-run without --dry-run to apply.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Apply reconciliation?')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($wallet, $missing) {
            // Refresh inside transaction
            $wallet->refresh();

            foreach ($missing as $tx) {
                $before = (float) $wallet->balance;
                $after  = $tx->type === 'credit'
                    ? $before + (float) $tx->net_amount
                    : $before - (float) $tx->net_amount;

                if ($after < 0) {
                    $this->warn("  Skipping {$tx->transaction_id} — would make balance negative (₹{$after})");
                    continue;
                }

                $wallet->transactions()->create([
                    'type'           => $tx->type,
                    'amount'         => $tx->net_amount,
                    'balance_before' => $before,
                    'balance_after'  => $after,
                    'description'    => 'Reconciled: ' . $tx->transaction_id,
                    'reference'      => $tx->transaction_id,
                    'performed_by'   => auth()->id() ?? 1,
                ]);

                $wallet->update(['balance' => $after]);
                $wallet->balance = $after; // keep local copy in sync
            }
        });

        $wallet->refresh();
        $this->info('Reconciliation complete. New wallet balance: ₹' . number_format($wallet->balance, 2));

        return self::SUCCESS;
    }
}
