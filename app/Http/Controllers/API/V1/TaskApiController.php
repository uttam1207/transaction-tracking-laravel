<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * @OA\Get(path="/api/v1/tasks", tags={"Tasks"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="List of tasks"))
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Task::with('assignedEmployee.user', 'assignedBy')
            ->withCount('comments');

        // Employees only see their own tasks
        if ($user->isEmployee()) {
            $employee = $user->employee;
            if (!$employee) {
                return response()->json(['data' => [], 'meta' => ['total' => 0]]);
            }
            $query->where('assigned_to', $employee->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $tasks->map(fn($t) => [
                'id'              => $t->id,
                'task_id'         => $t->task_id,
                'title'           => $t->title,
                'description'     => $t->description,
                'priority'        => $t->priority,
                'status'          => $t->status,
                'progress'        => $t->progress,
                'due_date'        => $t->due_date,
                'estimated_hours' => $t->estimated_hours,
                'actual_hours'    => $t->actual_hours,
                'comments_count'  => $t->comments_count,
                'assigned_to'     => $t->assignedEmployee ? [
                    'id'          => $t->assignedEmployee->id,
                    'employee_id' => $t->assignedEmployee->employee_id,
                    'full_name'   => $t->assignedEmployee->full_name,
                ] : null,
                'assigned_by'     => $t->assignedBy ? ['id' => $t->assignedBy->id, 'name' => $t->assignedBy->name] : null,
                'created_at'      => $t->created_at->toIso8601String(),
            ]),
            'meta' => [
                'total'        => $tasks->total(),
                'per_page'     => $tasks->perPage(),
                'current_page' => $tasks->currentPage(),
                'last_page'    => $tasks->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(path="/api/v1/tasks/{id}", tags={"Tasks"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Task details"))
     */
    public function show(Task $task)
    {
        $this->authorizeEmployee($task);
        $task->load('assignedEmployee.user', 'assignedBy', 'comments.user', 'timesheets');

        return response()->json(['data' => [
            'id'              => $task->id,
            'task_id'         => $task->task_id,
            'title'           => $task->title,
            'description'     => $task->description,
            'priority'        => $task->priority,
            'status'          => $task->status,
            'progress'        => $task->progress,
            'due_date'        => $task->due_date,
            'estimated_hours' => $task->estimated_hours,
            'actual_hours'    => $task->actual_hours,
            'tags'            => $task->tags,
            'comments'        => $task->comments->map(fn($c) => [
                'id'         => $c->id,
                'comment'    => $c->comment,
                'user'       => ['id' => $c->user->id, 'name' => $c->user->name],
                'created_at' => $c->created_at->toIso8601String(),
            ]),
            'total_logged_hours' => $task->timesheets->sum('hours'),
        ]]);
    }

    /**
     * @OA\Patch(path="/api/v1/tasks/{id}/status", tags={"Tasks"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Status updated"))
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeEmployee($task);

        $request->validate([
            'status'   => 'required|in:in_progress,review,completed',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $task->update([
            'status'   => $request->status,
            'progress' => $request->progress ?? ($request->status === 'completed' ? 100 : $task->progress),
        ]);

        return response()->json(['success' => true, 'message' => 'Task status updated.', 'data' => ['status' => $task->status]]);
    }

    /**
     * @OA\Post(path="/api/v1/tasks/{id}/comments", tags={"Tasks"}, security={{"sanctum":{}}},
     *   @OA\Response(response=201, description="Comment added"))
     */
    public function addComment(Request $request, Task $task)
    {
        $this->authorizeEmployee($task);

        $request->validate(['comment' => 'required|string|max:1000']);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return response()->json(['success' => true, 'data' => [
            'id'         => $comment->id,
            'comment'    => $comment->comment,
            'user'       => ['id' => auth()->id(), 'name' => auth()->user()->name],
            'created_at' => $comment->created_at->toIso8601String(),
        ]], 201);
    }

    private function authorizeEmployee(Task $task): void
    {
        $user = auth()->user();
        if ($user->isEmployee()) {
            $employee = $user->employee;
            abort_unless($employee && $task->assigned_to === $employee->id, 403, 'Access denied.');
        }
    }
}
