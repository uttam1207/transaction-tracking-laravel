<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // Cash or Bank — only applicable for Paid / Partial invoices
            $table->string('payment_mode', 10)->nullable()->after('payment_status')
                  ->comment('Cash or Bank — how money was received for Paid/Partial invoices');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
    }
};
