<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['Milk Buyer', 'Animal Buyer', 'Franchise Lead', 'Investor', 'Government Official', 'Veterinary Doctor'])->default('Milk Buyer');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('total_business_value', 12, 2)->default(0);
            $table->enum('status', ['Lead', 'Contacted', 'Active Customer', 'Partner', 'Inactive'])->default('Active Customer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_customers');
    }
};
