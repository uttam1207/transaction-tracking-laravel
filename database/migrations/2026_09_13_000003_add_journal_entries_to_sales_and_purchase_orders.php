<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')
                  ->nullable()
                  ->after('payment_status')
                  ->constrained('journal_entries')
                  ->nullOnDelete();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Entry when goods are received (DR Purchases, CR AP)
            $table->foreignId('journal_entry_id')
                  ->nullable()
                  ->after('invoice_path')
                  ->constrained('journal_entries')
                  ->nullOnDelete();
            // Entry when vendor is paid (DR AP, CR Bank)
            $table->foreignId('payment_journal_entry_id')
                  ->nullable()
                  ->after('journal_entry_id')
                  ->constrained('journal_entries')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('journal_entry_id');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_journal_entry_id');
            $table->dropConstrainedForeignId('journal_entry_id');
        });
    }
};