<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShiftController extends Controller
{
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
        $shifts      = Shift::where('is_active', true)->orderBy('name')->get();

        // Count employees per shift
        $shiftCounts = $shifts->mapWithKeys(fn($s) => [
            $s->key => Employee::where('status', 'active')
                ->whereJsonContains('shift_timing->type', $s->key)
                ->count()
        ]);

        return view('admin.shifts.index', compact('employees', 'departments', 'shifts', 'shiftCounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:80',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
            'color'      => 'nullable|string|max:20',
        ]);

        $key = Str::slug($request->name, '_');
        // Ensure unique key
        $base = $key;
        $i = 2;
        while (Shift::where('key', $key)->exists()) {
            $key = $base . '_' . $i++;
        }

        Shift::create([
            'key'        => $key,
            'name'       => $request->name,
            'start_time' => $request->start_time ?: null,
            'end_time'   => $request->end_time   ?: null,
            'color'      => $request->color ?? '#4f46e5',
            'is_active'  => true,
        ]);

        return back()->with('success', "Shift type '{$request->name}' created.");
    }

    public function updateShift(Request $request, Employee $employee)
    {
        $request->validate([
            'shift_type' => 'required|string',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
        ]);

        $shift = Shift::where('key', $request->shift_type)->first();
        $shiftData = $shift ? $shift->toShiftArray() : [
            'type' => $request->shift_type, 'label' => $request->shift_type,
            'start' => null, 'end' => null,
        ];

        if ($request->start_time) $shiftData['start'] = $request->start_time;
        if ($request->end_time)   $shiftData['end']   = $request->end_time;

        $employee->update(['shift_timing' => $shiftData]);

        return back()->with('success', $employee->full_name . "'s shift updated to '{$shiftData['label']}'.");
    }

    public function updateShiftType(Request $request, Shift $shift)
    {
        $request->validate([
            'name'       => 'required|string|max:80',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
            'color'      => 'nullable|string|max:20',
        ]);

        $shift->update([
            'name'       => $request->name,
            'start_time' => $request->start_time ?: null,
            'end_time'   => $request->end_time   ?: null,
            'color'      => $request->color ?? $shift->color,
        ]);

        return back()->with('success', "Shift type '{$shift->name}' updated.");
    }

    public function destroyShiftType(Shift $shift)
    {
        $shift->delete();
        return back()->with('success', "Shift type '{$shift->name}' deleted.");
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids' => 'nullable|string',
            'shift_type'   => 'required|string',
        ]);

        $shift = Shift::where('key', $request->shift_type)->first();
        $shiftData = $shift ? $shift->toShiftArray() : [
            'type' => $request->shift_type, 'label' => $request->shift_type,
            'start' => null, 'end' => null,
        ];

        // employee_ids arrives as a comma-separated string from the form
        $ids = array_filter(explode(',', $request->employee_ids ?? ''));

        if (empty($ids)) {
            Employee::where('status', 'active')->update(['shift_timing' => $shiftData]);
            return back()->with('success', 'Shift assigned to all active employees.');
        }

        Employee::whereIn('id', $ids)->update(['shift_timing' => $shiftData]);

        return back()->with('success', 'Shift assigned to ' . count($ids) . ' employee(s).');
    }
}
