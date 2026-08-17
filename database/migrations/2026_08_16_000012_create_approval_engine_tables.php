<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Approval Workflows ──────────────────────────────────────────────
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('module', [
                'leave', 'expense', 'purchase', 'attendance_correction',
                'employee_transfer', 'salary_revision', 'asset_request',
                'employee_promotion', 'exit_request',
            ]);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['module']); // one workflow per module
        });

        // ── Approval Steps ──────────────────────────────────────────────────
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedTinyInteger('step_number');     // 1, 2, 3 ...
            $table->string('step_name');                     // e.g. "Team Lead Approval"
            $table->enum('approver_type', [
                'specific_user',    // a named user
                'role',             // any user with this role string
                'department_head',  // resolved at runtime from dept
                'team_lead',        // resolved at runtime from team
                'hr',               // any user with role 'hr'
                'manager',          // direct manager of requester
            ]);
            $table->unsignedBigInteger('approver_user_id')->nullable(); // for specific_user
            $table->string('approver_role', 50)->nullable();            // for role type
            $table->boolean('is_final')->default(false);
            $table->boolean('is_optional')->default(false);
            $table->timestamps();

            $table->foreign('workflow_id')->references('id')->on('approval_workflows')->onDelete('cascade');
            $table->foreign('approver_user_id')->references('id')->on('users')->onDelete('set null');
            $table->unique(['workflow_id', 'step_number']);
        });

        // ── Approval Requests ───────────────────────────────────────────────
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            // Polymorphic — can point to Leave, Expense, EmployeeTransfer, etc.
            $table->morphs('requestable');
            $table->unsignedBigInteger('requested_by');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('workflow_id')->references('id')->on('approval_workflows')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            // morphs() already creates the composite index on (requestable_type, requestable_id)
        });

        // ── Approval Actions ────────────────────────────────────────────────
        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('step_id');
            $table->unsignedBigInteger('actor_id');
            $table->enum('action', ['approved', 'rejected', 'returned', 'escalated']);
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->useCurrent();
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('approval_requests')->onDelete('cascade');
            $table->foreign('step_id')->references('id')->on('approval_steps')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_workflows');
    }
};
