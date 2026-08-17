<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Purchase Requests ─────────────────────────────────────────────────
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number', 30)->unique();  // e.g. PR-2026-00001
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->date('required_date')->nullable();
            $table->text('purpose')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'converted'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'department_id']);
        });

        // ── Purchase Request Items ────────────────────────────────────────────
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_id');
            $table->string('item_name', 200);
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 30)->default('pcs');
            $table->decimal('estimated_unit_price', 15, 2)->nullable();
            $table->decimal('estimated_total', 15, 2)->nullable();
            $table->unsignedBigInteger('inventory_item_id')->nullable();  // link to inventory
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('cascade');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('set null');
        });

        // ── RFQs (Request for Quotation) ──────────────────────────────────────
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_number', 30)->unique();  // e.g. RFQ-2026-00001
            $table->unsignedBigInteger('purchase_request_id')->nullable();
            $table->date('due_date')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'sent', 'received', 'closed'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // ── RFQ Vendors (which vendors were invited to quote) ─────────────────
        Schema::create('rfq_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('vendor_id');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('rfq_id')->references('id')->on('rfqs')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unique(['rfq_id', 'vendor_id']);
        });

        // ── Vendor Quotations ─────────────────────────────────────────────────
        Schema::create('vendor_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number', 30)->unique();
            $table->unsignedBigInteger('rfq_id')->nullable();
            $table->unsignedBigInteger('vendor_id');
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['received', 'shortlisted', 'selected', 'rejected'])->default('received');
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('rfq_id')->references('id')->on('rfqs')->onDelete('set null');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // ── Vendor Quotation Items ────────────────────────────────────────────
        Schema::create('vendor_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_quotation_id');
            $table->string('item_name', 200);
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 30)->default('pcs');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('vendor_quotation_id')->references('id')->on('vendor_quotations')->onDelete('cascade');
        });

        // ── Goods Receipts ─────────────────────────────────────────────────────
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number', 30)->unique();  // Goods Receipt Note
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->date('receipt_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'partial'])->default('draft');
            $table->unsignedBigInteger('received_by');
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('set null');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('cascade');
        });

        // ── Goods Receipt Items ───────────────────────────────────────────────
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goods_receipt_id');
            $table->unsignedBigInteger('inventory_item_id')->nullable();
            $table->string('item_name', 200);
            $table->decimal('ordered_qty', 10, 2)->default(0);
            $table->decimal('received_qty', 10, 2);
            $table->string('unit', 30)->default('pcs');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('goods_receipt_id')->references('id')->on('goods_receipts')->onDelete('cascade');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('vendor_quotation_items');
        Schema::dropIfExists('vendor_quotations');
        Schema::dropIfExists('rfq_vendors');
        Schema::dropIfExists('rfqs');
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
    }
};
