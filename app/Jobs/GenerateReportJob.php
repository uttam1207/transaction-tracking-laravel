<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class GenerateReportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public string $reportType,
        public array $filters,
        public User $requestedBy
    ) {}

    public function handle(): void
    {
        // Generate report data based on type
        $data = match ($this->reportType) {
            'transactions' => $this->generateTransactionReport(),
            'attendance'   => $this->generateAttendanceReport(),
            'employees'    => $this->generateEmployeeReport(),
            default        => [],
        };

        // In a real implementation, save to storage and notify the user
        \Log::info("Report generated: {$this->reportType}", ['user' => $this->requestedBy->id]);
    }

    private function generateTransactionReport(): array
    {
        return \App\Models\Transaction::whereBetween('created_at', [
            $this->filters['from_date'] ?? now()->startOfMonth(),
            $this->filters['to_date'] ?? now(),
        ])->get()->toArray();
    }

    private function generateAttendanceReport(): array
    {
        return \App\Models\Attendance::whereMonth('date', $this->filters['month'] ?? now()->month)
            ->whereYear('date', $this->filters['year'] ?? now()->year)
            ->with('employee')
            ->get()->toArray();
    }

    private function generateEmployeeReport(): array
    {
        return \App\Models\Employee::with('user', 'department')->active()->get()->toArray();
    }
}
