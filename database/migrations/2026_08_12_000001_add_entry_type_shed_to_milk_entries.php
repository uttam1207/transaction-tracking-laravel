<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('milk_entries', function (Blueprint $table) {
            $table->enum('entry_type', ['per_animal', 'per_shed', 'entire_farm'])
                  ->default('per_animal')
                  ->after('animal_id');
            $table->string('shed_number', 100)
                  ->nullable()
                  ->after('entry_type');
        });

        // Existing records with no animal_id were "batch" entries → treat as entire_farm
        DB::table('milk_entries')
            ->whereNull('animal_id')
            ->update(['entry_type' => 'entire_farm']);
    }

    public function down(): void
    {
        Schema::table('milk_entries', function (Blueprint $table) {
            $table->dropColumn(['entry_type', 'shed_number']);
        });
    }
};
