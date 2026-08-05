<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breeds', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('animal_type')->default('Buffalo');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default breeds
        DB::table('breeds')->insert([
            ['name' => 'Murrah',      'animal_type' => 'Buffalo', 'description' => 'Most popular dairy buffalo breed. High milk yield.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bhadawari',   'animal_type' => 'Buffalo', 'description' => 'Known for high fat content in milk.',                 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jafarabadi',  'animal_type' => 'Buffalo', 'description' => 'Heaviest buffalo breed, good milk producer.',          'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nili-Ravi',   'animal_type' => 'Buffalo', 'description' => 'High milk-yielding breed from Punjab region.',         'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Surti',       'animal_type' => 'Buffalo', 'description' => 'Medium-sized buffalo, good for milk production.',      'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mehsana',     'animal_type' => 'Buffalo', 'description' => 'Cross between Surti and Murrah buffalo.',              'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Crossbred',   'animal_type' => 'Buffalo', 'description' => 'Cross-bred buffalo for improved productivity.',        'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gir',         'animal_type' => 'Cow',     'description' => 'Indian indigenous dairy cow, A2 milk.',               'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sahiwal',     'animal_type' => 'Cow',     'description' => 'Best Indian dairy cow, heat tolerant.',               'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HF Crossbred','animal_type' => 'Cow',     'description' => 'Holstein Friesian cross, high milk yield.',           'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('breeds');
    }
};
