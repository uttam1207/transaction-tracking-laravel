<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Multi-department employee support ───────────────────────────────
        Schema::create('employee_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('department_id');
            $table->boolean('is_primary')->default(false);
            $table->string('role_in_department', 100)->nullable();
            $table->date('started_at');
            $table->date('ended_at')->nullable(); // null = current
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->index(['employee_id', 'is_primary']);
        });

        // Migrate existing department_id data into employee_departments
        DB::statement("
            INSERT INTO employee_departments (employee_id, department_id, is_primary, started_at, created_at, updated_at)
            SELECT id, department_id, 1, joining_date, NOW(), NOW()
            FROM employees
            WHERE department_id IS NOT NULL
        ");

        // ── Reporting Hierarchy History ─────────────────────────────────────
        Schema::create('employee_reporting_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('reports_to_employee_id')->nullable();
            $table->enum('relationship_type', [
                'direct_manager', 'team_lead', 'department_head', 'hr',
            ])->default('direct_manager');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('reports_to_employee_id')->references('id')->on('employees')->onDelete('set null');
        });

        // ── Employee Lifecycle Events ───────────────────────────────────────
        Schema::create('employee_lifecycle_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->enum('event_type', [
                'hired', 'onboarding_started', 'onboarding_completed',
                'probation_started', 'probation_extended', 'probation_passed',
                'confirmed', 'promoted', 'transferred', 'department_changed',
                'role_changed', 'salary_revised', 'resigned', 'terminated',
                'rehired', 'exit_completed',
            ]);
            $table->date('event_date');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('triggered_by')->nullable(); // user who logged this
            $table->json('metadata')->nullable(); // flexible data: {from_dept, to_dept, etc.}
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('triggered_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['employee_id', 'event_date']);
        });

        // ── Employee Assets ─────────────────────────────────────────────────
        Schema::create('employee_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('asset_name');
            $table->string('asset_code')->unique();
            $table->enum('category', [
                'laptop', 'phone', 'tablet', 'vehicle',
                'furniture', 'equipment', 'access_card', 'other',
            ])->default('other');
            $table->string('serial_number')->nullable();
            $table->text('description')->nullable();
            $table->date('issued_date');
            $table->date('return_date')->nullable();
            $table->string('condition_on_issue', 50)->default('good'); // good/fair/poor
            $table->string('condition_on_return', 50)->nullable();
            $table->enum('status', ['issued', 'returned', 'damaged', 'lost'])->default('issued');
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_assets');
        Schema::dropIfExists('employee_lifecycle_events');
        Schema::dropIfExists('employee_reporting_history');
        Schema::dropIfExists('employee_departments');
    }
};
