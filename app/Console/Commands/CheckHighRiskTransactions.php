<?php

namespace App\Console\Commands;

use App\Models\FraudAlert;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckHighRiskTransactions extends Command
{
    protected $signature   = 'fraud:check-high-risk {--threshold=80 : Risk score threshold}';
    protected $description = 'Alert admins about high-risk transactions that still have no fraud alert';

    public function handle(NotificationService $notificationService): int
    {
        $threshold = (int) $this->option('threshold');

        $unalerted = Transaction::where('risk_score', '>=', $threshold)
            ->where('is_flagged', true)
            ->whereDoesntHave('fraudAlerts', fn($q) => $q->whereIn('status', ['open', 'investigating']))
            ->latest()
            ->limit(100)
            ->get();

        if ($unalerted->isEmpty()) {
            $this->info('No unalerted high-risk transactions found.');
            return self::SUCCESS;
        }

        $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'admin']))->get();

        foreach ($unalerted as $tx) {
            FraudAlert::firstOrCreate(
                ['transaction_id' => $tx->id, 'status' => 'open'],
                [
                    'alert_type'  => 'high_risk_score',
                    'severity'    => $tx->risk_score >= 90 ? 'critical' : 'high',
                    'risk_score'  => $tx->risk_score,
                    'description' => "Transaction {$tx->transaction_id} has risk score {$tx->risk_score} (auto-detected).",
                ]
            );
        }

        foreach ($admins as $admin) {
            $notificationService->send(
                $admin,
                'High-Risk Transactions Detected',
                "{$unalerted->count()} high-risk transaction(s) require review.",
                'danger', [], '/admin/fraud-alerts'
            );
        }

        $this->info("Processed {$unalerted->count()} high-risk transaction(s).");
        return self::SUCCESS;
    }
}
