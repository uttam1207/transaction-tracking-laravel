<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Task Assignees (multi-assignee, replaces single assigned_to) ────
        Schema::create('task_assignees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('employee_id');
            $table->enum('role', ['primary', 'collaborator', 'reviewer'])->default('collaborator');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['task_id', 'employee_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // ── Task Watchers ───────────────────────────────────────────────────
        Schema::create('task_watchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->unique(['task_id', 'user_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ── Task Dependencies ───────────────────────────────────────────────
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');          // the dependent task
            $table->unsignedBigInteger('depends_on_id');    // must be done first
            $table->enum('type', [
                'finish_to_start',   // B can start only after A finishes (most common)
                'start_to_start',    // B can start only after A starts
                'finish_to_finish',  // B can finish only after A finishes
            ])->default('finish_to_start');
            $table->timestamps();

            $table->unique(['task_id', 'depends_on_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('depends_on_id')->references('id')->on('tasks')->onDelete('cascade');
        });

        // ── Task Attachments ────────────────────────────────────────────────
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('original_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->string('mime_type', 100)->nullable();
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });

        // ── Extend tasks table ──────────────────────────────────────────────
        Schema::table('tasks', function (Blueprint $table) {
            // Milestone FK (nullable — tasks without milestones still allowed)
            $table->unsignedBigInteger('milestone_id')->nullable()->after('project_id');
            // Explicit start date (separate from started_at timestamp)
            $table->date('start_date')->nullable()->after('due_date');

            $table->foreign('milestone_id')->references('id')->on('milestones')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['milestone_id']);
            $table->dropColumn(['milestone_id', 'start_date']);
        });
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('task_watchers');
        Schema::dropIfExists('task_assignees');
    }
};
