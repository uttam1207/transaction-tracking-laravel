<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── CRM Leads ─────────────────────────────────────────────────────────
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);                         // Contact name
            $table->string('company', 200)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('source', 80)->nullable();            // e.g. website, referral, cold-call
            $table->enum('status', [
                'new', 'contacted', 'qualified', 'proposal',
                'negotiation', 'won', 'lost',
            ])->default('new');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->decimal('deal_value', 15, 2)->nullable();
            $table->date('expected_close')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('converted_customer_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('converted_customer_id')->references('id')->on('crm_customers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'assigned_to']);
        });

        // ── Lead Activities (calls, meetings, emails) ─────────────────────────
        Schema::create('crm_lead_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->enum('type', ['call', 'email', 'meeting', 'note', 'demo', 'proposal'])->default('note');
            $table->string('subject', 200)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('activity_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('crm_leads')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // ── Pipeline Stages (configurable) ────────────────────────────────────
        Schema::create('crm_pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->string('color', 20)->default('#6c757d');
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_pipeline_stages');
        Schema::dropIfExists('crm_lead_activities');
        Schema::dropIfExists('crm_leads');
    }
};
