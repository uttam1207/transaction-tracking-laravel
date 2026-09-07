<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        // Seed the default categories
        $defaults = [
            'Feed Supplier',
            'Medicine Supplier',
            'Equipment Supplier',
            'Fodder Supplier',
            'Veterinary',
            'Other',
        ];

        foreach ($defaults as $name) {
            DB::table('vendor_categories')->insert([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_categories');
    }
};
