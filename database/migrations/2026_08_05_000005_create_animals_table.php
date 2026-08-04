<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->string('tag_number')->unique();
            $table->string('name')->nullable();
            $table->string('breed')->default('Murrah');
            $table->date('dob')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 12, 2)->default(0);
            $table->decimal('current_weight', 8, 2)->nullable();
            $table->unsignedInteger('lactation_number')->default(0);
            $table->enum('health_status', ['Healthy', 'Sick', 'Under Treatment'])->default('Healthy');
            $table->enum('pregnancy_status', ['Open', 'Inseminated', 'Pregnant', 'Dry'])->default('Open');
            $table->string('owner_name')->default('ASDairy');
            $table->string('shed_number')->default('Shed No. 1');
            $table->enum('status', ['Active', 'Sold', 'Deceased'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};
