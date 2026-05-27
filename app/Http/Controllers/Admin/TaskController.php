<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Employee;
use App\Models\Project;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function index(Request $request)
    {
        $query = Task::with('assignedTo.user', 'assignedBy', 'project');

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('task_id', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->employee_id) {
            $query->where('assigned_to', $request->employee_id);
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();
        $employees = Employee::with('user')->active()->get();
        $projects = Project::active()->get();

        return view('admin.tasks.index', compact('tasks', 'employees', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:employees,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date|after:today',
            'project_id' => 'nullable|exists:projects,id',
            'estimated_hours' => 'nullable|integer|min:0',
        ]);

        $task = Task::create(array_merge(
            $request->only(['title', 'description', 'assigned_to', 'priority', 'due_date', 'project_id', 'estimated_hours']),
            [
                'assigned_by' => auth()->id(),
                'status' => 'pending',
                'task_id' => 'TASK-' . strtoupper(uniqid()),
            ]
        ));

        $employee = Employee::find($request->assigned_to);
        if ($employee) {
            $this->notificationService->sendTaskAssigned($employee->user, [
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Task created.', 'task' => $task->load('assignedTo.user')]);
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,review,approved,rejected,completed,cancelled',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $task->update($request->only([
            'title', 'description', 'priority', 'status', 'due_date',
            'estimated_hours', 'progress', 'rejection_reason'
        ]));

        if ($request->status === 'completed' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'Task updated.']);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['success' => true, 'message' => 'Task deleted.']);
    }

    public function kanban(Request $request)
    {
        $statuses = ['pending', 'in_progress', 'review', 'completed'];
        $tasks = [];

        foreach ($statuses as $status) {
            $tasks[$status] = Task::with('assignedTo.user')
                ->where('status', $status)
                ->latest()
                ->get();
        }

        return view('admin.tasks.kanban', compact('tasks'));
    }

    public function approve(Request $request, Task $task)
    {
        $task->update(['status' => 'approved', 'completed_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Task approved.']);
    }

    public function reject(Request $request, Task $task)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        $task->update(['status' => 'rejected', 'rejection_reason' => $request->rejection_reason]);
        return response()->json(['success' => true, 'message' => 'Task rejected.']);
    }
}
