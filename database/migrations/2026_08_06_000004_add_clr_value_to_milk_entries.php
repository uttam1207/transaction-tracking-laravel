<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('milk_entries', function (Blueprint $table) {
            $table->decimal('clr_value', 5, 2)->nullable()->after('snf_percentage')
                  ->comment('Corrected Lactometer Reading (typically 26–32)');
        });
    }

    public function down(): void
    {
        Schema::table('milk_entries', function (Blueprint $table) {
            $table->dropColumn('clr_value');
        });
    }
};