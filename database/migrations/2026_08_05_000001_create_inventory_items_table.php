<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', [
                'Medicine',
                'Feed',
                'Equipment',
                'Consumables',
                'Stationery',
                'Uniforms',
                'Cleaning Material',
                'Miscellaneous'
            ])->default('Feed');
            $table->string('unit')->default('kg'); // kg, liters, bottles, pcs, bags, etc.
            $table->decimal('min_stock', 12, 2)->default(0); // Low stock alert threshold
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
