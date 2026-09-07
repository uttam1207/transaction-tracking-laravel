<?php

namespace App\Console\Commands;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringTasks extends Command
{
    protected $signature   = 'tasks:create-recurring';
    protected $description = 'Spawn the next instance of each completed recurring task';

    public function handle(): int
    {
        $generated = 0;

        // Find every recurring task that has been completed
        Task::where('is_recurring', true)
            ->where('status', 'completed')
            ->each(function (Task $task) use (&$generated) {

                // Determine the root ID (parent or self)
                $rootId = $task->parent_task_id ?? $task->id;

                // Skip if there is already an active (non-completed/cancelled) instance
                $hasActive = Task::where('is_recurring', true)
                    ->whereIn('status', ['pending', 'in_progress', 'review', 'assigned'])
                    ->where(function ($q) use ($rootId) {
                        $q->where('parent_task_id', $rootId)
                          ->orWhere('id', $rootId);
                    })
                    ->exists();

                if ($hasActive) {
                    return;
                }

                // Calculate next due date from the task's due_date
                $base    = $task->due_date ?? Carbon::today();
                $nextDue = match ($task->recurrence_type) {
                    'daily'   => $base->copy()->addDay(),
                    'weekly'  => $base->copy()->addWeek(),
                    'monthly' => $base->copy()->addMonth(),
                    'yearly'  => $base->copy()->addYear(),
                    default   => $base->copy()->addWeek(),
                };

                // Stop if recurrence has expired
                if ($task->recurring_ends_at && $nextDue->gt($task->recurring_ends_at)) {
                    return;
                }

                Task::create([
                    'task_id'           => 'TASK-' . strtoupper(uniqid()),
                    'title'             => $task->title,
                    'description'       => $task->description,
                    'assigned_to'       => $task->assigned_to,
                    'assigned_by'       => $task->assigned_by,
                    'project_id'        => $task->project_id,
                    'milestone_id'      => $task->milestone_id,
                    'priority'          => $task->priority,
                    'status'            => 'pending',
                    'due_date'          => $nextDue,
                    'estimated_hours'   => $task->estimated_hours,
                    'tags'              => $task->tags,
                    'is_recurring'      => true,
                    'recurrence_type'   => $task->recurrence_type,
                    'recurring_ends_at' => $task->recurring_ends_at,
                    'parent_task_id'    => $rootId,
                ]);

                $generated++;
                $this->line("  ↻ Spawned next instance of: {$task->title} (due {$nextDue->toDateString()})");
            });

        $this->info("Recurring tasks generated: {$generated}");
        return self::SUCCESS;
    }
}