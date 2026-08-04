<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vendors
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('category')->default('Feed & Fodder'); // Feed, Medicine, Machinery, Consumables
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // Purchase Orders (Procurement)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->date('order_date');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['Draft', 'Sent', 'Received', 'Paid'])->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Sales Orders (Sales)
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('crm_customer_id')->nullable()->constrained('crm_customers')->onDelete('set null');
            $table->enum('item_type', ['Milk Sales', 'Animal Sales', 'Feed Sales', 'Dung Sales', 'Franchise Royalty']);
            $table->date('sale_date');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('rate', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_status', ['Paid', 'Pending', 'Partial'])->default('Paid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('vendors');
    }
};
