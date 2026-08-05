<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('inventory_categories')->insert([
            ['name' => 'Feed',             'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Medicine',         'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Equipment',        'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Consumables',      'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cleaning Material','is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Stationery',       'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Uniforms',         'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Miscellaneous',    'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_categories');
    }
};