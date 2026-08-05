<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('is_system')->default(false)
                  ->comment('System types cannot be deleted');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the 9 default system action types
        $now = now();
        $types = [
            'Vaccination', 'Deworming', 'Heat Detection', 'AI',
            'Pregnancy Check', 'Calving', 'Dry Off', 'Sale', 'Death',
        ];
        foreach ($types as $type) {
            DB::table('action_types')->insert([
                'name'       => $type,
                'is_system'  => true,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('action_types');
    }
};