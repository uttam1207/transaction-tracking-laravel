<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month_number');   // 1–12
            $table->string('month_label', 10);             // e.g. "Aug 2026"
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('hra', 10, 2)->default(0);
            $table->decimal('other_allowances', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('pf_deduction', 10, 2)->default(0);
            $table->decimal('tax_deduction', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->decimal('days_worked', 5, 1)->default(0);
            $table->decimal('days_absent', 5, 1)->default(0);
            $table->enum('payment_status', ['Pending', 'Processing', 'Paid'])->default('Pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_mode', 50)->nullable();  // Bank Transfer, Cash, UPI
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'year', 'month_number'], 'emp_salary_month_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
