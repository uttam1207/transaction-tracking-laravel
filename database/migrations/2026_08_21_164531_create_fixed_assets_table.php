<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 30)->unique();
            $table->string('name', 200);
            $table->string('asset_type', 50); // Land, Building, Vehicle, Machinery, Equipment, Furniture, Computer, Other
            $table->text('description')->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('location', 200)->nullable();
            $table->string('vendor', 200)->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_value', 15, 2);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->unsignedTinyInteger('useful_life_years')->default(5);
            $table->enum('depreciation_method', ['straight_line', 'reducing_balance', 'none'])->default('straight_line');
            $table->decimal('depreciation_rate', 6, 2)->nullable(); // annual %, auto-computed or manual override
            $table->string('status', 30)->default('active'); // active, disposed, under_maintenance, written_off
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_value', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
