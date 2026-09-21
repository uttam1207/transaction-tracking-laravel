<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // Links a SalesOrder to the Transaction that auto-generated it.
            // NULL for invoices created manually through the Sales Invoice form.
            $table->foreignId('transaction_id')
                  ->nullable()
                  ->after('journal_entry_id')
                  ->constrained('transactions')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
        });
    }
};
