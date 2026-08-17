<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LeaveRequestController extends Controller implements HasMiddleware
{
    public function __construct(private NotificationService $notificationService) {}

    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                $user = auth()->user();
                if (!$user || !in_array($user->role, ['super_admin', 'admin'])) {
                    abort(403, 'Access restricted to administrators.');
                }
                return $next($request);
            }),
        ];
    }

    public function index(Request $request)
    {
        $query = Leave::with('employee.user', 'employee.department', 'approver');

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->department_id) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->from) {
            $query->where('from_date', '>=', $request->from);
        }
        if ($request->to) {
            $query->where('to_date', '<=', $request->to);
        }

        $leaves = $query->latest()->paginate(20)->withQueryString();

        // Stats (all time, no scope)
        $stats = [
            'pending'  => Leave::where('status', 'pending')->count(),
            'approved' => Leave::where('status', 'approved')->count(),
            'rejected' => Leave::where('status', 'rejected')->count(),
            'this_month' => Leave::whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->count(),
        ];

        $departments = Department::active()->orderBy('name')->get();
        $employees   = Employee::with('user')->active()->orderBy('id')->get();

        $leaveTypes = ['annual', 'sick', 'casual', 'maternity', 'paternity', 'unpaid', 'other'];

        return view('admin.leave-requests.index', compact('leaves', 'stats', 'departments', 'employees', 'leaveTypes'));
    }

    public function action(Request $request, Leave $leave)
    {
        $request->validate([
            'action'           => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);

        if ($leave->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending leaves can be actioned.'], 422);
        }

        $status = $request->action === 'approve' ? 'approved' : 'rejected';

        $leave->update([
            'status'           => $status,
            'approved_by'      => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
            'actioned_at'      => now(),
        ]);

        // If approved, mark attendance as on_leave for each weekday
        if ($status === 'approved') {
            $date = $leave->from_date->copy();
            while ($date->lte($leave->to_date)) {
                if (!$date->isWeekend()) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $leave->employee_id, 'date' => $date->format('Y-m-d')],
                        ['status' => 'on_leave']
                    );
                }
                $date->addDay();
            }
        }

        // Notify the employee about the decision
        $leave->load('employee.user');
        $employeeUser = $leave->employee->user ?? null;
        if ($employeeUser) {
            $leaveType = ucfirst($leave->type);
            $dateRange = $leave->from_date->format('d M Y');
            if ($leave->from_date->ne($leave->to_date)) {
                $dateRange .= ' – ' . $leave->to_date->format('d M Y');
            }

            if ($status === 'approved') {
                $this->notificationService->send(
                    $employeeUser,
                    'Leave Request Approved',
                    "Your {$leaveType} leave request ({$dateRange}) has been approved.",
                    'success',
                    [],
                    route('employee.attendance.leaves'),
                );
            } else {
                $reason = $request->rejection_reason ? " Reason: {$request->rejection_reason}" : '';
                $this->notificationService->send(
                    $employeeUser,
                    'Leave Request Rejected',
                    "Your {$leaveType} leave request ({$dateRange}) has been rejected.{$reason}",
                    'danger',
                    [],
                    route('employee.attendance.leaves'),
                );
            }
        }

        return response()->json(['success' => true, 'message' => "Leave {$status} successfully."]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'leave_ids'   => 'required|array',
            'leave_ids.*' => 'exists:leaves,id',
            'action'      => 'required|in:approve,reject',
        ]);

        $status  = $request->action === 'approve' ? 'approved' : 'rejected';
        $updated = 0;

        foreach ($request->leave_ids as $id) {
            $leave = Leave::where('id', $id)->where('status', 'pending')->first();
            if (!$leave) continue;

            $leave->update([
                'status'      => $status,
                'approved_by' => auth()->id(),
                'actioned_at' => now(),
            ]);

            if ($status === 'approved') {
                $date = $leave->from_date->copy();
                while ($date->lte($leave->to_date)) {
                    if (!$date->isWeekend()) {
                        Attendance::updateOrCreate(
                            ['employee_id' => $leave->employee_id, 'date' => $date->format('Y-m-d')],
                            ['status' => 'on_leave']
                        );
                    }
                    $date->addDay();
                }
            }

            // Notify employee
            $leave->load('employee.user');
            $employeeUser = $leave->employee->user ?? null;
            if ($employeeUser) {
                $leaveType = ucfirst($leave->type);
                $dateRange = $leave->from_date->format('d M Y');
                if ($leave->from_date->ne($leave->to_date)) {
                    $dateRange .= ' – ' . $leave->to_date->format('d M Y');
                }
                $notifType = $status === 'approved' ? 'success' : 'danger';
                $notifTitle = $status === 'approved' ? 'Leave Request Approved' : 'Leave Request Rejected';
                $notifMsg   = "Your {$leaveType} leave request ({$dateRange}) has been {$status}.";
                $this->notificationService->send($employeeUser, $notifTitle, $notifMsg, $notifType, [], route('employee.attendance.leaves'));
            }

            $updated++;
        }

        return response()->json(['success' => true, 'message' => "{$updated} leave request(s) {$status}."]);
    }
}
