<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('color', 20)->default('#4f46e5');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default shifts
        DB::table('shifts')->insert([
            ['key' => 'morning',  'name' => 'Morning Shift', 'start_time' => '07:00:00', 'end_time' => '15:00:00', 'color' => '#f59e0b', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'day',      'name' => 'Day Shift',     'start_time' => '09:00:00', 'end_time' => '17:00:00', 'color' => '#3b82f6', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'evening',  'name' => 'Evening Shift', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'color' => '#8b5cf6', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'night',    'name' => 'Night Shift',   'start_time' => '22:00:00', 'end_time' => '06:00:00', 'color' => '#6366f1', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'flexible', 'name' => 'Flexible Hours','start_time' => null,        'end_time' => null,        'color' => '#10b981', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
