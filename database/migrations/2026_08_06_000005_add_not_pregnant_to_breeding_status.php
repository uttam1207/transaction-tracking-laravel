<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE breeding_records MODIFY COLUMN status
                ENUM('Heat Detected','AI Done','Confirmed Pregnant','Calved','Repeat Breeder','Not Pregnant')
                NOT NULL DEFAULT 'Heat Detected'");
        }
    }

    public function down(): void
    {
        DB::table('breeding_records')
            ->where('status', 'Not Pregnant')
            ->update(['status' => 'Repeat Breeder']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE breeding_records MODIFY COLUMN status
                ENUM('Heat Detected','AI Done','Confirmed Pregnant','Calved','Repeat Breeder')
                NOT NULL DEFAULT 'Heat Detected'");
        }
    }
};