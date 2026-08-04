<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_records', function (Blueprint $table) {
            $table->id();
            $table->string('plot_name'); // Plot A, Plot B, Anoo Main Field
            $table->string('crop_type')->default('Napier Grass');
            $table->date('plantation_date')->nullable();
            $table->string('fertilizer_used')->nullable();
            $table->string('pesticide_used')->nullable();
            $table->date('harvest_date')->nullable();
            $table->decimal('yield_kg', 10, 2)->default(0);
            $table->decimal('diesel_liters', 8, 2)->default(0);
            $table->decimal('electricity_units', 8, 2)->default(0);
            $table->decimal('water_usage_liters', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_records');
    }
};
