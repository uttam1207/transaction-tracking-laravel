<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milk_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('shift', ['Morning', 'Evening']);
            $table->foreignId('animal_id')->nullable()->constrained('animals')->onDelete('cascade');
            $table->decimal('quantity_liters', 8, 2);
            $table->decimal('fat_percentage', 5, 2)->default(7.5);
            $table->decimal('snf_percentage', 5, 2)->default(9.0);
            $table->string('quality_rating')->default('Grade A');
            $table->decimal('rejected_liters', 8, 2)->default(0);
            $table->foreignId('recorded_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_entries');
    }
};
