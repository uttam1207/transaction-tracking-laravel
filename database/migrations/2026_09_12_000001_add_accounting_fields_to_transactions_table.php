<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Chart of Accounts links — which account to debit and which to credit
            $table->unsignedBigInteger('debit_account_id')->nullable()->after('user_id')
                  ->comment('Account to debit when this transaction is posted');
            $table->unsignedBigInteger('credit_account_id')->nullable()->after('debit_account_id')
                  ->comment('Account to credit when this transaction is posted');

            // Back-link to the auto-generated journal entry
            $table->unsignedBigInteger('journal_entry_id')->nullable()->after('credit_account_id')
                  ->comment('Journal entry auto-created when this transaction was posted to accounts');

            $table->foreign('debit_account_id')
                  ->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('credit_account_id')
                  ->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('journal_entry_id')
                  ->references('id')->on('journal_entries')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['debit_account_id']);
            $table->dropForeign(['credit_account_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['debit_account_id', 'credit_account_id', 'journal_entry_id']);
        });
    }
};