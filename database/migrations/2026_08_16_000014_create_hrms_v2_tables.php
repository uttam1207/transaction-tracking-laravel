<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Employee Transfers ──────────────────────────────────────────────
        Schema::create('employee_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('from_department_id')->nullable();
            $table->unsignedBigInteger('to_department_id')->nullable();
            $table->unsignedBigInteger('from_branch_id')->nullable();
            $table->unsignedBigInteger('to_branch_id')->nullable();
            $table->date('effective_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('from_department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('to_department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('from_branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('to_branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // ── Employee Promotions ─────────────────────────────────────────────
        Schema::create('employee_promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('from_designation_id')->nullable();
            $table->unsignedBigInteger('to_designation_id')->nullable();
            $table->date('effective_date');
            $table->decimal('salary_before', 12, 2)->nullable();
            $table->decimal('salary_after', 12, 2)->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('from_designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('to_designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // ── Recruitment Jobs ────────────────────────────────────────────────
        Schema::create('recruitment_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('job_code')->unique();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->unsignedTinyInteger('vacancies')->default(1);
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('employment_type', 50)->default('full_time');
            $table->decimal('salary_range_min', 12, 2)->nullable();
            $table->decimal('salary_range_max', 12, 2)->nullable();
            $table->date('posted_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['draft', 'open', 'on_hold', 'closed', 'filled'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // ── Recruitment Applications ────────────────────────────────────────
        Schema::create('recruitment_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone', 20)->nullable();
            $table->string('resume_path')->nullable();
            $table->text('cover_letter')->nullable();
            $table->enum('stage', [
                'applied', 'screening', 'shortlisted',
                'interview_scheduled', 'interviewed',
                'offer_sent', 'hired', 'rejected',
            ])->default('applied');
            $table->text('notes')->nullable();
            $table->decimal('offered_salary', 12, 2)->nullable();
            $table->date('interview_date')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('recruitment_jobs')->onDelete('cascade');
            $table->index(['job_id', 'stage']);
        });

        // ── Exit Interviews ─────────────────────────────────────────────────
        Schema::create('exit_interviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('resignation_date')->nullable();
            $table->date('last_working_date')->nullable();
            $table->unsignedTinyInteger('notice_period_days')->default(30);
            $table->enum('reason', [
                'better_opportunity', 'personal_reasons', 'relocation',
                'health_issues', 'further_studies', 'dissatisfied',
                'compensation', 'work_environment', 'other',
            ])->nullable();
            $table->text('detailed_reason')->nullable();
            $table->date('exit_interview_date')->nullable();
            $table->unsignedBigInteger('interviewer_id')->nullable();
            $table->text('feedback')->nullable();
            $table->tinyInteger('satisfaction_rating')->nullable(); // 1-5
            $table->boolean('rehire_eligible')->default(true);
            $table->enum('status', ['initiated', 'interview_scheduled', 'completed'])->default('initiated');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('set null');
        });

        // ── Onboarding Task Templates ───────────────────────────────────────
        Schema::create('onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'document', 'training', 'equipment', 'system_access',
                'introduction', 'policy_review', 'other',
            ])->default('other');
            $table->boolean('is_required')->default(true);
            $table->unsignedTinyInteger('due_after_days')->default(7); // due X days after joining
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Employee Onboarding (instances per employee) ────────────────────
        Schema::create('employee_onboarding', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('onboarding_task_id');
            $table->unsignedBigInteger('assigned_to')->nullable(); // user responsible
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('onboarding_task_id')->references('id')->on('onboarding_tasks')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->unique(['employee_id', 'onboarding_task_id']);
        });

        // ── Training Programs ───────────────────────────────────────────────
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('trainer')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('duration_hours', 5, 1)->default(0);
            $table->boolean('is_mandatory')->default(false);
            $table->enum('mode', ['online', 'offline', 'hybrid'])->default('offline');
            $table->string('venue')->nullable();
            $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // ── Employee Training Enrollments ───────────────────────────────────
        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('training_program_id');
            $table->enum('status', ['enrolled', 'attending', 'completed', 'failed', 'withdrawn'])->default('enrolled');
            $table->decimal('score', 5, 2)->nullable();
            $table->string('certificate_path')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('training_program_id')->references('id')->on('training_programs')->onDelete('cascade');
            $table->unique(['employee_id', 'training_program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_trainings');
        Schema::dropIfExists('training_programs');
        Schema::dropIfExists('employee_onboarding');
        Schema::dropIfExists('onboarding_tasks');
        Schema::dropIfExists('exit_interviews');
        Schema::dropIfExists('recruitment_applications');
        Schema::dropIfExists('recruitment_jobs');
        Schema::dropIfExists('employee_promotions');
        Schema::dropIfExists('employee_transfers');
    }
};
