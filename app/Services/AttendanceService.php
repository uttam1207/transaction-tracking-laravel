<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class AttendanceService
{
    public function checkIn(Employee $employee): Attendance
    {
        $today = today();

        // Check if already checked in today
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->check_in) {
            throw new \Exception('Already checked in today.');
        }

        $checkInTime = now();
        $workStart = Carbon::parse($today->format('Y-m-d') . ' ' . ($employee->shift_timing['start'] ?? '09:00'));
        $status = $checkInTime->gt($workStart->addMinutes(15)) ? 'late' : 'present';

        return Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'check_in' => $checkInTime,
                'status' => $status,
                'check_in_ip' => Request::ip(),
            ]
        );
    }

    public function checkOut(Employee $employee): Attendance
    {
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->firstOrFail();

        $checkOutTime = now();
        $workHours = $attendance->check_in->diffInMinutes($checkOutTime) / 60;
        $standardHours = 8;
        $overtimeHours = max(0, $workHours - $standardHours);

        $attendance->update([
            'check_out' => $checkOutTime,
            'work_hours' => round($workHours, 2),
            'overtime_hours' => round($overtimeHours, 2),
            'check_out_ip' => Request::ip(),
        ]);

        return $attendance;
    }

    public function getMonthlyReport(Employee $employee, int $month, int $year): array
    {
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $workingDays = $this->getWorkingDaysInMonth($month, $year);

        return [
            'total_days' => $workingDays,
            'present' => $attendance->where('status', 'present')->count(),
            'absent' => $workingDays - $attendance->whereIn('status', ['present', 'half_day', 'late'])->count(),
            'half_day' => $attendance->where('status', 'half_day')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'on_leave' => $attendance->where('status', 'on_leave')->count(),
            'total_hours' => round($attendance->sum('work_hours'), 2),
            'overtime_hours' => round($attendance->sum('overtime_hours'), 2),
            'attendance_percentage' => $workingDays > 0
                ? round(($attendance->whereIn('status', ['present', 'late', 'half_day'])->count() / $workingDays) * 100, 1)
                : 0,
        ];
    }

    private function getWorkingDaysInMonth(int $month, int $year): int
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $days = 0;

        while ($startDate->lte($endDate)) {
            if (!$startDate->isWeekend() && !Holiday::isHoliday($startDate)) {
                $days++;
            }
            $startDate->addDay();
        }

        return $days;
    }
}
