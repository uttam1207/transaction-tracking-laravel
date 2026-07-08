<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Timesheet;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth()->user()->employee;

        $query = Task::with('project', 'assignedBy')->where('assigned_to', $employee->id);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('task_id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'pending'     => Task::where('assigned_to', $employee->id)->whereIn('status', ['pending', 'assigned'])->count(),
            'in_progress' => Task::where('assigned_to', $employee->id)->where('status', 'in_progress')->count(),
            'review'      => Task::where('assigned_to', $employee->id)->where('status', 'review')->count(),
            'completed'   => Task::where('assigned_to', $employee->id)->where('status', 'completed')->count(),
            'overdue'     => Task::where('assigned_to', $employee->id)
                                ->whereNotIn('status', ['completed', 'cancelled'])
                                ->whereNotNull('due_date')
                                ->where('due_date', '<', now())
                                ->count(),
        ];

        return view('employee.tasks.index', compact('tasks', 'stats'));
    }

    public function show(Task $task)
    {
        $this->authorizeTask($task);
        $task->load('project', 'assignedBy', 'comments.user', 'timesheets');

        $employee = auth()->user()->employee;
        $activeTimer = $employee
            ? Timesheet::where('employee_id', $employee->id)
                ->where('task_id', $task->id)
                ->where('status', 'running')
                ->first()
            : null;

        return view('employee.tasks.show', compact('task', 'activeTimer'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $request->validate([
            'status' => 'required|in:in_progress,review,completed',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $updates = ['status' => $request->status, 'progress' => $request->progress ?? $task->progress];

        if ($request->status === 'in_progress' && !$task->started_at) {
            $updates['started_at'] = now();
        }

        if ($request->status === 'completed') {
            $updates['completed_at'] = now();
            $updates['progress'] = 100;
        }

        $task->update($updates);

        return response()->json(['success' => true, 'message' => 'Task status updated.']);
    }

    public function addComment(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user'),
        ]);
    }

    public function startTimer(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $employee = auth()->user()->employee;

        // Stop any running timers first
        Timesheet::where('employee_id', $employee->id)->where('status', 'running')
            ->update(['status' => 'completed', 'end_time' => now(),
                'hours' => \DB::raw('TIMESTAMPDIFF(MINUTE, start_time, NOW()) / 60')]);

        $timesheet = Timesheet::create([
            'employee_id' => $employee->id,
            'task_id' => $task->id,
            'project_id' => $task->project_id,
            'date' => today(),
            'start_time' => now(),
            'status' => 'running',
        ]);

        $task->update(['status' => 'in_progress', 'started_at' => $task->started_at ?? now()]);

        return response()->json(['success' => true, 'timesheet_id' => $timesheet->id]);
    }

    public function stopTimer(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $employee = auth()->user()->employee;

        $timesheet = Timesheet::where('employee_id', $employee->id)
            ->where('task_id', $task->id)
            ->where('status', 'running')
            ->first();

        if (!$timesheet) {
            return response()->json(['success' => false, 'message' => 'No running timer found.'], 422);
        }

        $hours = $timesheet->start_time->diffInMinutes(now()) / 60;
        $timesheet->update([
            'end_time' => now(),
            'hours' => round($hours, 2),
            'status' => 'completed',
            'description' => $request->description,
        ]);

        $task->update(['actual_hours' => $task->actual_hours + round($hours, 2)]);

        return response()->json([
            'success' => true,
            'hours' => round($hours, 2),
            'message' => 'Timer stopped. ' . round($hours, 2) . ' hours logged.',
        ]);
    }

    private function authorizeTask(Task $task): void
    {
        $employee = auth()->user()->employee;
        if (!$employee || $task->assigned_to !== $employee->id) {
            if (!auth()->user()->isAdmin() && !auth()->user()->isManager()) {
                abort(403, 'You are not authorized to view this task.');
            }
        }
    }
}
