<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Machinery & Maintenance
        Schema::create('machine_maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('machine_name'); // Milking Machine, Generator, Pump, Tractor, Vehicle
            $table->enum('maintenance_type', ['Preventive Schedule', 'Service History', 'Breakdown Log']);
            $table->date('service_date');
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('serviced_by')->nullable();
            $table->text('description')->nullable();
            $table->date('next_service_due')->nullable();
            $table->timestamps();
        });

        // Compliance Documents
        Schema::create('compliance_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_title');
            $table->enum('category', ['FSSAI', 'Animal Insurance', 'Vaccination Certificates', 'Government Licenses', 'Bank Loan Documents', 'Land Records', 'Audit Files']);
            $table->string('document_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['Active', 'Expiring Soon', 'Expired'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_documents');
        Schema::dropIfExists('machine_maintenances');
    }
};
