<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->boolean('born_in_farm')->default(false)->after('status')
                  ->comment('true = born in own farm, false = purchased from outside');
            $table->string('purchase_from')->nullable()->after('born_in_farm')
                  ->comment('Seller name / location when purchased from outside');
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropColumn(['born_in_farm', 'purchase_from']);
        });
    }
};