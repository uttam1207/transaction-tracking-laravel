<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── departments: add branch_id ────────────────────────────────────────
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        // ── employees: add shift_id, designation_id, team_id ─────────────────
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_id')->nullable()->after('designation');
            $table->unsignedBigInteger('designation_id')->nullable()->after('shift_id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('department_id');

            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        // ── feed_plans: add inventory_item_id FK + is_active ─────────────────
        Schema::table('feed_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_item_id')->nullable()->after('animal_group_id');
            $table->boolean('is_active')->default(true)->after('quantity_per_animal_kg');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('set null');
        });

        // ── animals: add group_id FK ──────────────────────────────────────────
        Schema::table('animals', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('shed_number');
            $table->foreign('group_id')->references('id')->on('animal_groups')->onDelete('set null');
        });

        // ── inventory_items: add current_stock (denormalized) ─────────────────
        if (!Schema::hasColumn('inventory_items', 'current_stock')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->decimal('current_stock', 12, 2)->default(0)->after('min_stock');
            });
        }

        // Populate current_stock from existing stock_movements
        $items = DB::table('inventory_items')->get();
        foreach ($items as $item) {
            $stockIn  = DB::table('stock_movements')
                ->where('inventory_item_id', $item->id)
                ->where('type', 'in')
                ->sum('quantity');
            $stockOut = DB::table('stock_movements')
                ->where('inventory_item_id', $item->id)
                ->where('type', 'out')
                ->sum('quantity');
            $adjustInc = DB::table('stock_movements')
                ->where('inventory_item_id', $item->id)
                ->where('type', 'adjustment')
                ->where('reason', 'like', '%Increase%')
                ->sum('quantity');
            $adjustDec = DB::table('stock_movements')
                ->where('inventory_item_id', $item->id)
                ->where('type', 'adjustment')
                ->where('reason', 'like', '%Decrease%')
                ->sum('quantity');

            $currentStock = max(0, ($stockIn + $adjustInc) - ($stockOut + $adjustDec));
            DB::table('inventory_items')->where('id', $item->id)->update(['current_stock' => $currentStock]);
        }
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });

        Schema::table('feed_plans', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->dropColumn(['inventory_item_id', 'is_active']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['shift_id', 'designation_id', 'branch_id']);
            $table->dropColumn(['shift_id', 'designation_id', 'branch_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('current_stock');
        });
    }
};
