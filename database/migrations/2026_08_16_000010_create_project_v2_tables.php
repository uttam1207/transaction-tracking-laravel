<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Project Members (normalized, replaces team_members JSON) ────────
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('role', 50)->default('member'); // member, lead, reviewer
            $table->boolean('is_manager')->default(false);
            $table->date('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'employee_id']);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // ── Project Teams pivot ─────────────────────────────────────────────
        Schema::create('project_teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('team_id');
            $table->timestamps();

            $table->unique(['project_id', 'team_id']);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });

        // ── Project Departments pivot ───────────────────────────────────────
        Schema::create('project_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('department_id');
            $table->timestamps();

            $table->unique(['project_id', 'department_id']);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });

        // ── Milestones ─────────────────────────────────────────────────────
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'missed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });

        // ── Extend projects table ───────────────────────────────────────────
        Schema::table('projects', function (Blueprint $table) {
            $table->string('client')->nullable()->after('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->after('status');
            $table->unsignedTinyInteger('progress')->default(0)->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['client', 'priority', 'progress']);
        });
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('project_departments');
        Schema::dropIfExists('project_teams');
        Schema::dropIfExists('project_members');
    }
};
