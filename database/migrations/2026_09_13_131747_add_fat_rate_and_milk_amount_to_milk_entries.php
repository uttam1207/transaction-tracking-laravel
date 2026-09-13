<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('milk_entries', function (Blueprint $table) {
            $table->decimal('fat_rate', 8, 2)->nullable()->after('fat_percentage')
                  ->comment('Rate per fat point per litre (optional)');
            $table->decimal('milk_amount', 10, 2)->nullable()->after('fat_rate')
                  ->comment('Calculated value: quantity × fat_percentage × fat_rate');
        });
    }

    public function down(): void
    {
        Schema::table('milk_entries', function (Blueprint $table) {
            $table->dropColumn(['fat_rate', 'milk_amount']);
        });
    }
};
