<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Financial Periods (Fiscal Years / Months) ─────────────────────────
        Schema::create('financial_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                 // e.g. "FY 2026-27", "April 2026"
            $table->enum('type', ['year', 'quarter', 'month'])->default('month');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closed', 'locked'])->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // ── Chart of Accounts ─────────────────────────────────────────────────
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();         // e.g. 1000, 1001, 4000
            $table->string('name', 200);
            $table->enum('type', [
                'asset',        // 1xxx — Cash, AR, Inventory, Fixed Assets
                'liability',    // 2xxx — AP, Loans
                'equity',       // 3xxx — Owner equity, Retained earnings
                'revenue',      // 4xxx — Sales, Service income
                'expense',      // 5xxx — Salaries, Rent, Feed cost
            ]);
            $table->string('sub_type', 50)->nullable();   // e.g. current_asset, fixed_asset
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_direct_posting')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->index(['type', 'is_active']);
        });

        // ── Journal Entries (header) ──────────────────────────────────────────
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 30)->unique();   // e.g. JE-2026-00001
            $table->unsignedBigInteger('period_id')->nullable();
            $table->date('entry_date');
            $table->string('reference', 100)->nullable();   // External reference (invoice no, etc.)
            $table->enum('type', [
                'general',
                'sales',
                'purchase',
                'payment',
                'receipt',
                'payroll',
                'depreciation',
                'adjustment',
            ])->default('general');
            $table->text('description')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'reversed', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('reversal_of')->nullable(); // points to original entry
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('financial_periods')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('posted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reversal_of')->references('id')->on('journal_entries')->onDelete('set null');
            $table->index(['status', 'entry_date']);
        });

        // ── Journal Entry Lines (debit / credit) ──────────────────────────────
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('cost_center_id', 50)->nullable();  // optional cost center tag
            $table->timestamps();

            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
        });

        // ── Ledger Balances (pre-computed per account per period) ─────────────
        Schema::create('ledger_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('period_id');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
            $table->foreign('period_id')->references('id')->on('financial_periods')->onDelete('cascade');
            $table->unique(['account_id', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_balances');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('financial_periods');
    }
};
