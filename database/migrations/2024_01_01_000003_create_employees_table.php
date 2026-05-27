<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('employee_id')->unique();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('designation');
            $table->string('team')->nullable();
            $table->date('joining_date');
            $table->date('leaving_date')->nullable();
            $table->string('employment_type')->default('full_time'); // full_time, part_time, contract
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('salary_currency')->default('USD');
            $table->string('bank_account')->nullable();
            $table->string('tax_id')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('work_location')->default('office'); // office, remote, hybrid
            $table->json('shift_timing')->nullable();
            $table->integer('annual_leave_balance')->default(21);
            $table->integer('sick_leave_balance')->default(10);
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
