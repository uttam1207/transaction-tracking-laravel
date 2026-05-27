<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    public function __construct(private AttendanceService $attendanceService)
    {
    }

    public function index(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->when($request->month, fn($q) => $q->whereMonth('date', $request->month))
            ->when($request->year, fn($q) => $q->whereYear('date', $request->year))
            ->orderBy('date', 'desc')
            ->paginate(31);

        return response()->json(['success' => true, 'data' => $attendance]);
    }

    public function checkIn(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        try {
            $attendance = $this->attendanceService->checkIn($employee);
            return response()->json([
                'success' => true,
                'message' => 'Checked in successfully!',
                'data' => $attendance,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function checkOut(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        try {
            $attendance = $this->attendanceService->checkOut($employee);
            return response()->json([
                'success' => true,
                'message' => 'Checked out successfully!',
                'data' => $attendance,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function today(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $attendance = Attendance::where('employee_id', $employee->id)->whereDate('date', today())->first();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => today()->format('Y-m-d'),
                'status' => $attendance?->status ?? 'not_marked',
                'check_in' => $attendance?->check_in?->format('H:i'),
                'check_out' => $attendance?->check_out?->format('H:i'),
                'work_hours' => $attendance?->work_hours ?? 0,
                'is_checked_in' => $attendance && $attendance->check_in && !$attendance->check_out,
            ],
        ]);
    }
}
