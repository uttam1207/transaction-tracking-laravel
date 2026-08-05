<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change action_type column from ENUM to VARCHAR so it can hold any ActionType name
        DB::statement("ALTER TABLE animal_actions MODIFY COLUMN action_type VARCHAR(100) NOT NULL");

        Schema::table('animal_actions', function (Blueprint $table) {
            $table->string('document_path')->nullable()->after('notes')
                  ->comment('Uploaded document, PDF or image file path (relative to storage/app/public)');
        });
    }

    public function down(): void
    {
        Schema::table('animal_actions', function (Blueprint $table) {
            $table->dropColumn('document_path');
        });

        // Restore the original ENUM — values that no longer match will need manual cleanup
        DB::statement("ALTER TABLE animal_actions MODIFY COLUMN action_type ENUM(
            'Vaccination','Deworming','Heat Detection','AI',
            'Pregnancy Check','Calving','Dry Off','Sale','Death'
        ) NOT NULL");
    }
};