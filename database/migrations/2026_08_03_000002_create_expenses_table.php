<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('expense_date');
            $table->foreignId('expense_category_id')->constrained('expense_categories');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('vendor_payee')->nullable();
            $table->enum('payment_method', ['cash', 'upi', 'bank_transfer', 'cheque'])->default('cash');
            $table->enum('payment_status', ['paid', 'pending'])->default('paid');
            $table->string('reference_number')->nullable();
            $table->string('bill_path')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
