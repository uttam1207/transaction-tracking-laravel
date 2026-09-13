<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // FK to the new dynamic item types table (nullable to keep old rows valid)
            $table->foreignId('sale_item_type_id')
                  ->nullable()
                  ->after('item_type')
                  ->constrained('sale_item_types')
                  ->nullOnDelete();

            // Milk-specific pricing fields
            $table->decimal('fat_percentage', 5, 2)->nullable()->after('rate');
            $table->decimal('fat_rate', 10, 2)->nullable()->after('fat_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sale_item_type_id');
            $table->dropColumn(['fat_percentage', 'fat_rate']);
        });
    }
};