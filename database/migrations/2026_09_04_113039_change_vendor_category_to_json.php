<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1 — migrate existing string values to JSON arrays before changing the column type
        DB::table('vendors')->whereNotNull('category')->get()->each(function ($vendor) {
            $decoded = json_decode($vendor->category, true);
            if (! is_array($decoded)) {
                // Plain string — wrap in an array
                DB::table('vendors')->where('id', $vendor->id)
                    ->update(['category' => json_encode([$vendor->category])]);
            }
        });

        // Step 2 — change the column type to JSON
        DB::statement('ALTER TABLE vendors MODIFY COLUMN category JSON NULL');
    }

    public function down(): void
    {
        // Revert: change back to VARCHAR and collapse array to first element
        DB::statement('ALTER TABLE vendors MODIFY COLUMN category VARCHAR(80) NULL');

        DB::table('vendors')->whereNotNull('category')->get()->each(function ($vendor) {
            $arr = json_decode($vendor->category, true);
            $plain = is_array($arr) ? ($arr[0] ?? null) : $vendor->category;
            DB::table('vendors')->where('id', $vendor->id)
                ->update(['category' => $plain]);
        });
    }
};
