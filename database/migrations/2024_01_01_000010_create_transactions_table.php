<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('category');
            $table->string('type')->default('debit'); // debit, credit
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('USD');
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'cancelled', 'reversed'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_account')->nullable();
            $table->string('sender_bank')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_account')->nullable();
            $table->string('receiver_bank')->nullable();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('country')->nullable();
            $table->string('device_id')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->integer('risk_score')->default(0);
            $table->string('fraud_reason')->nullable();
            $table->boolean('is_refunded')->default(false);
            $table->unsignedBigInteger('refund_transaction_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
