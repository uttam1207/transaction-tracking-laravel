<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->string('franchise_code')->unique();
            $table->string('owner_name');
            $table->string('location');
            $table->string('contact_number');
            $table->date('agreement_date');
            $table->decimal('investment_amount', 12, 2)->default(0);
            $table->enum('status', ['Applied', 'Approved', 'Active', 'Suspended'])->default('Active');
            $table->decimal('royalty_percentage', 5, 2)->default(5.0);
            $table->decimal('milk_collected_liters', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('franchises');
    }
};
