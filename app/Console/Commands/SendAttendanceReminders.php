<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendAttendanceReminders extends Command
{
    protected $signature   = 'attendance:send-reminders {--type=checkin : checkin or checkout}';
    protected $description = 'Send attendance check-in / check-out reminders to employees';

    public function handle(NotificationService $notificationService): int
    {
        $type = $this->option('type');

        $employees = Employee::where('status', 'active')->with('user')->get();
        $count = 0;

        foreach ($employees as $employee) {
            if (!$employee->user) continue;

            if ($type === 'checkin') {
                $alreadyIn = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', today())->exists();

                if (!$alreadyIn) {
                    $notificationService->send(
                        $employee->user,
                        'Attendance Reminder',
                        'You have not checked in today. Please mark your attendance.',
                        'warning', [], '/employee/attendance'
                    );
                    $count++;
                }
            } elseif ($type === 'checkout') {
                $notOut = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', today())
                    ->whereNotNull('check_in')
                    ->whereNull('check_out')
                    ->exists();

                if ($notOut) {
                    $notificationService->send(
                        $employee->user,
                        'Check-Out Reminder',
                        'Please check out before end of your work day.',
                        'info', [], '/employee/attendance'
                    );
                    $count++;
                }
            }
        }

        $this->info("Sent {$count} {$type} reminder(s).");
        return self::SUCCESS;
    }
}
