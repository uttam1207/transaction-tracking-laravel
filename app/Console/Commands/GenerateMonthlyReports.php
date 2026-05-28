<?php

namespace App\Console\Commands;

use App\Jobs\GenerateReportJob;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateMonthlyReports extends Command
{
    protected $signature   = 'reports:generate-monthly {--type=all : transactions|employees|attendance|all}';
    protected $description = 'Dispatch monthly report generation jobs for all admins';

    public function handle(): int
    {
        $type = $this->option('type');

        $types = $type === 'all'
            ? ['transactions', 'employees', 'attendance']
            : [$type];

        $filters = [
            'month' => now()->subMonth()->month,
            'year'  => now()->subMonth()->year,
        ];

        foreach ($types as $reportType) {
            GenerateReportJob::dispatch($reportType, $filters);
            $this->info("Dispatched {$reportType} report job for " . now()->subMonth()->format('F Y'));
        }

        // Notify super admins
        $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'admin']))->get();
        foreach ($admins as $admin) {
            app(\App\Services\NotificationService::class)->send(
                $admin,
                'Monthly Reports Ready',
                'Monthly reports for ' . now()->subMonth()->format('F Y') . ' are being generated.',
                'info', [], '/admin/reports/transactions'
            );
        }

        return self::SUCCESS;
    }
}
