<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_group_id')->constrained('animal_groups')->onDelete('cascade');
            $table->string('feed_item_name'); // Green Fodder, Dry Fodder, Concentrate, Mineral Mixture
            $table->decimal('quantity_per_animal_kg', 8, 2)->default(0); // Daily requirement per animal in kg
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_plans');
    }
};
