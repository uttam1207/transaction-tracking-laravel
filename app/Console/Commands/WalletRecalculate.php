<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WalletRecalculate extends Command
{
    protected $signature = 'wallet:recalculate
                            {--dry-run : Show what the balance would be without writing}
                            {--force  : Skip confirmation prompt}
                            {--zero   : Set wallet to ₹0.00 (full reset, ignores transactions)}';

    protected $description = 'Recalculate company wallet balance from all active (non-deleted) success transactions';

    public function handle(): int
    {
        $wallet = Wallet::company();
        $old    = (float) $wallet->balance;

        $this->info("Current wallet balance : ₹" . number_format($old, 2));

        if ($this->option('zero')) {
            // Hard reset to 0
            $newBalance = 0.00;
            $this->warn('--zero flag set: wallet will be reset to ₹0.00 regardless of transactions.');
        } else {
            // Recalculate from all active success transactions
            $credits = Transaction::where('status', 'success')->where('type', 'credit')->sum('net_amount');
            $debits  = Transaction::where('status', 'success')->where('type', 'debit')->sum('net_amount');
            $newBalance = (float) $credits - (float) $debits;

            $this->line("Active success credits : ₹" . number_format($credits, 2));
            $this->line("Active success debits  : ₹" . number_format($debits,  2));
            $this->line("Calculated balance     : ₹" . number_format($newBalance, 2));
        }

        $diff = $newBalance - $old;
        if ($diff == 0) {
            $this->info('Wallet is already correct — no change needed.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn(sprintf(
            'Balance will change: ₹%s  →  ₹%s  (difference: %s₹%s)',
            number_format($old, 2),
            number_format($newBalance, 2),
            $diff >= 0 ? '+' : '-',
            number_format(abs($diff), 2)
        ));

        if ($this->option('dry-run')) {
            $this->warn('[DRY RUN] No changes written. Re-run without --dry-run to apply.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Apply this recalculation to the wallet?')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($wallet, $old, $newBalance, $diff) {
            $wallet->update(['balance' => $newBalance]);

            // Log the correction in wallet_transactions
            WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => $diff >= 0 ? 'credit' : 'debit',
                'amount'         => abs($diff),
                'balance_before' => $old,
                'balance_after'  => $newBalance,
                'description'    => 'wallet:recalculate — balance corrected from ₹' . number_format($old, 2) . ' to ₹' . number_format($newBalance, 2),
                'reference'      => 'RECALC-' . now()->format('YmdHis'),
                'performed_by'   => 1, // system / super admin
            ]);
        });

        $this->info('Done. New wallet balance: ₹' . number_format($newBalance, 2));
        return self::SUCCESS;
    }
}
