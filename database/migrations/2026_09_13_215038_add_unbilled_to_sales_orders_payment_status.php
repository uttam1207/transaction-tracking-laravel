<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales_orders MODIFY payment_status ENUM('Paid','Pending','Partial','Unbilled') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        // Remove Unbilled rows before reverting to old enum (to avoid truncation)
        DB::statement("UPDATE sales_orders SET payment_status = 'Pending' WHERE payment_status = 'Unbilled'");
        DB::statement("ALTER TABLE sales_orders MODIFY payment_status ENUM('Paid','Pending','Partial') NOT NULL DEFAULT 'Pending'");
    }
};