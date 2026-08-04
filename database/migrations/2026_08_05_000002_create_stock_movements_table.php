<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->decimal('quantity', 12, 2);
            $table->date('date');
            $table->string('source_purpose')->nullable(); // Purchase, Harvest, Feed, Treatment, Maintenance, Damage, etc.
            $table->string('issued_to_or_vendor')->nullable(); // Employee, Department, or Vendor
            $table->string('reason')->nullable(); // For adjustments: Damage, Expired, Physical Count
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
