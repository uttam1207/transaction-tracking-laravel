<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Salary Components ─────────────────────────────────────────────────
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->enum('type', ['earning', 'deduction'])->default('earning');
            $table->enum('calculation_type', ['fixed', 'percentage_of_basic', 'percentage_of_gross'])
                  ->default('fixed');
            $table->decimal('default_value', 10, 2)->nullable()
                  ->comment('Fixed amount or percentage (e.g. 20 = 20%)');
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_taxable')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ── Salary Structures ─────────────────────────────────────────────────
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->unsignedBigInteger('employee_id')->nullable()
                  ->comment('Per-employee override; null = dept/org level');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->boolean('is_default')->default(false);
            $table->date('effective_from');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        // ── Salary Structure Items (pivot) ────────────────────────────────────
        Schema::create('salary_structure_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_structure_id')->constrained('salary_structures')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->decimal('value', 10, 2)->default(0)
                  ->comment('Amount (if fixed) or percentage (if percentage_of_*)');
            $table->timestamps();

            $table->unique(['salary_structure_id', 'salary_component_id'], 'ssi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_structure_items');
        Schema::dropIfExists('salary_structures');
        Schema::dropIfExists('salary_components');
    }
};
