<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\LoginHistory;
use Illuminate\Console\Command;

class CleanupOldLogs extends Command
{
    protected $signature   = 'logs:cleanup {--days=90 : Delete logs older than N days}';
    protected $description = 'Delete activity logs, audit logs, and login histories older than N days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $activity = ActivityLog::where('created_at', '<', $cutoff)->delete();
        $audit    = AuditLog::where('created_at', '<', $cutoff)->delete();
        $login    = LoginHistory::where('created_at', '<', $cutoff)->delete();

        $this->info("Cleaned up logs older than {$days} days:");
        $this->line("  ActivityLog: {$activity} records deleted");
        $this->line("  AuditLog:    {$audit} records deleted");
        $this->line("  LoginHistory:{$login} records deleted");

        return self::SUCCESS;
    }
}
