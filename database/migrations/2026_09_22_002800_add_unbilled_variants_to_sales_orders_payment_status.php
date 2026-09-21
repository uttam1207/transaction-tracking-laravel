<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the payment_status ENUM to include the two new unbilled variants:
        //   UnbilledPaid    → goods delivered, full amount already received
        //   UnbilledPartial → goods delivered, amount partially received
        DB::statement("ALTER TABLE sales_orders MODIFY payment_status
            ENUM('Paid','Pending','Partial','Unbilled','UnbilledPaid','UnbilledPartial')
            NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        // Revert any rows that use the new values before shrinking the ENUM
        DB::statement("UPDATE sales_orders SET payment_status = 'Unbilled'
            WHERE payment_status IN ('UnbilledPaid','UnbilledPartial')");

        DB::statement("ALTER TABLE sales_orders MODIFY payment_status
            ENUM('Paid','Pending','Partial','Unbilled')
            NOT NULL DEFAULT 'Pending'");
    }
};
