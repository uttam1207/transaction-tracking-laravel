<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * amount_paid tracks how much cash has actually been received against this invoice.
     *
     * Rules:
     *   payment_status = 'Paid'     → amount_paid = total_amount
     *   payment_status = 'Partial'  → amount_paid = X  (0 < X < total_amount)
     *   payment_status = 'Pending'  → amount_paid = 0
     *   payment_status = 'Unbilled' → amount_paid = 0
     *
     * This allows proper AR calculation:
     *   Outstanding = total_amount - amount_paid
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->default(0)->after('total_amount');
        });

        // Backfill: Paid invoices → amount_paid = total_amount
        DB::statement('UPDATE sales_orders SET amount_paid = total_amount WHERE payment_status = "Paid"');
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }
};