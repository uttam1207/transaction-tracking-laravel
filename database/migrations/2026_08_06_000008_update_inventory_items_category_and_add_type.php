<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change category from ENUM to VARCHAR(100) so it can be admin-managed
        DB::statement("ALTER TABLE inventory_items MODIFY COLUMN category VARCHAR(100) NOT NULL DEFAULT 'Feed'");

        // Add item_type column for sub-classification
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('item_type', 100)->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('item_type');
        });

        DB::statement("ALTER TABLE inventory_items MODIFY COLUMN category
            ENUM('Medicine','Feed','Equipment','Consumables','Stationery','Uniforms','Cleaning Material','Miscellaneous')
            NOT NULL DEFAULT 'Feed'");
    }
};