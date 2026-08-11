<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CreateRecurringTasks extends Command
{
    protected $signature   = 'tasks:create-recurring';
    protected $description = 'Spawn today\'s instances of all active recurring tasks';

    public function handle(): int
    {
        $today = today();
        $created = 0;
        $skipped = 0;

        // Only look at template (parent) recurring tasks that are still active
        $templates = Task::where('is_recurring', true)
            ->whereNull('parent_task_id')          // templates only, not spawned children
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($today) {
                $q->whereNull('recurring_ends_at')
                  ->orWhere('recurring_ends_at', '>=', $today);
            })
            ->get();

        foreach ($templates as $template) {
            // Determine the target date for today's instance
            $shouldSpawn = match ($template->recurrence_type) {
                'daily'   => true,
                'weekly'  => $today->dayOfWeek === Carbon::parse($template->created_at)->dayOfWeek,
                'monthly' => $today->day === Carbon::parse($template->created_at)->day,
                default   => false,
            };

            if (! $shouldSpawn) {
                $skipped++;
                continue;
            }

            // Skip if a child task for today already exists (prevent duplicates)
            $alreadyExists = Task::where('parent_task_id', $template->id)
                ->whereDate('created_at', $today)
                ->exists();

            if ($alreadyExists) {
                $skipped++;
                continue;
            }

            Task::create([
                'task_id'         => 'TASK-' . strtoupper(uniqid()),
                'parent_task_id'  => $template->id,
                'title'           => $template->title,
                'description'     => $template->description,
                'assigned_to'     => $template->assigned_to,
                'assigned_by'     => $template->assigned_by,
                'project_id'      => $template->project_id,
                'priority'        => $template->priority,
                'status'          => 'pending',
                'due_date'        => $today,          // due same day it's spawned
                'estimated_hours' => $template->estimated_hours,
                'is_recurring'    => false,            // child tasks are not templates
                'progress'        => 0,
            ]);

            $created++;
            $this->line("  Created: [{$template->task_id}] {$template->title}");
        }

        $this->info("Recurring tasks: {$created} created, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
