<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_item_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('inventory_item_types')->insert([
            ['name' => 'Green Fodder',    'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dry Fodder',      'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Concentrate',     'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mineral Mixture', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Vaccine',         'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Injectable',      'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Oral Medicine',   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'General',         'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_item_types');
    }
};