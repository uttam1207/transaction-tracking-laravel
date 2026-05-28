<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    // Predefined shift templates stored in settings/config
    private array $defaultShifts = [
        'morning'   => ['label' => 'Morning Shift',   'start' => '07:00', 'end' => '15:00'],
        'day'       => ['label' => 'Day Shift',        'start' => '09:00', 'end' => '17:00'],
        'evening'   => ['label' => 'Evening Shift',    'start' => '14:00', 'end' => '22:00'],
        'night'     => ['label' => 'Night Shift',      'start' => '22:00', 'end' => '06:00'],
        'flexible'  => ['label' => 'Flexible Hours',   'start' => null,    'end' => null],
    ];

    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department'])
            ->where('status', 'active');

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->shift) {
            $query->whereJsonContains('shift_timing->type', $request->shift);
        }

        $employees   = $query->latest()->paginate(20);
        $departments = Department::orderBy('name')->get();
        $shifts      = $this->defaultShifts;

        // Count by shift type
        $shiftCounts = collect($this->defaultShifts)->map(function ($shift, $key) {
            return Employee::where('status', 'active')
                ->whereJsonContains('shift_timing->type', $key)
                ->count();
        });

        return view('admin.shifts.index', compact('employees', 'departments', 'shifts', 'shiftCounts'));
    }

    public function updateShift(Request $request, Employee $employee)
    {
        $request->validate([
            'shift_type' => 'required|string',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
        ]);

        $shiftData = $this->defaultShifts[$request->shift_type] ?? ['label' => $request->shift_type];
        $shiftData['type'] = $request->shift_type;

        if ($request->start_time) $shiftData['start'] = $request->start_time;
        if ($request->end_time)   $shiftData['end']   = $request->end_time;

        $employee->update(['shift_timing' => $shiftData]);

        return back()->with('success', $employee->full_name . "'s shift updated to '{$shiftData['label']}'.");
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'shift_type'   => 'required|string',
        ]);

        $shiftData = $this->defaultShifts[$request->shift_type]
            ?? ['label' => $request->shift_type, 'type' => $request->shift_type];
        $shiftData['type'] = $request->shift_type;

        Employee::whereIn('id', $request->employee_ids)->update(['shift_timing' => $shiftData]);

        return back()->with('success', 'Shift assigned to ' . count($request->employee_ids) . ' employee(s).');
    }
}
