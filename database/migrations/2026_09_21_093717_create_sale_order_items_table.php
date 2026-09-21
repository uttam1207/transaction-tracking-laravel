<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('sale_item_type_id')->nullable()->constrained('sale_item_types')->nullOnDelete();
            $table->string('item_type', 100);
            $table->string('description', 500)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('fat_percentage', 6, 2)->nullable();
            $table->decimal('fat_rate', 10, 2)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Backfill one item per existing sales order (including soft-deleted)
        DB::statement("
            INSERT INTO sale_order_items
                (sales_order_id, sale_item_type_id, item_type, quantity, rate,
                 fat_percentage, fat_rate, amount, sort_order, created_at, updated_at)
            SELECT id, sale_item_type_id, item_type, quantity, rate,
                   fat_percentage, fat_rate, total_amount, 0, created_at, updated_at
            FROM sales_orders
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_order_items');
    }
};