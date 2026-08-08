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
        Schema::table('animals', function (Blueprint $table) {
            $table->enum('animal_type', ['Cow', 'Buffalo', 'Bull', 'Heifer', 'Calf'])
                  ->default('Cow')
                  ->after('tag_number')
                  ->comment('Distinguishes Cow, Buffalo, Bull, Heifer, Calf for feed group sync');
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropColumn('animal_type');
        });
    }
};
