<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Performance Reviews ───────────────────────────────────────────────
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedBigInteger('reviewer_id');
            $table->string('period', 20)->comment('e.g. "Q3 2026", "Annual 2026"');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('overall_rating', 3, 1)->nullable()
                  ->comment('1.0 – 5.0 scale');
            $table->enum('status', ['draft', 'submitted', 'acknowledged', 'closed'])
                  ->default('draft');
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals_for_next_period')->nullable();
            $table->text('employee_comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->foreign('reviewer_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // ── Performance Goals ─────────────────────────────────────────────────
        Schema::create('performance_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_review_id')
                  ->constrained('performance_reviews')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('target_value', 100)->nullable();
            $table->string('achieved_value', 100)->nullable();
            $table->unsignedTinyInteger('rating')->nullable()
                  ->comment('1-5 scale per goal');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'missed'])
                  ->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_goals');
        Schema::dropIfExists('performance_reviews');
    }
};
