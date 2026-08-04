<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            $table->enum('record_type', ['Vaccination', 'Deworming', 'Treatment', 'Doctor Visit', 'Emergency']);
            $table->date('date');
            $table->string('disease_symptoms')->nullable();
            $table->string('treatment_given')->nullable();
            $table->string('medicine_used')->nullable();
            $table->string('vet_doctor_name')->nullable();
            $table->decimal('body_temp', 4, 1)->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->enum('status', ['Scheduled', 'Completed', 'Followup Required'])->default('Completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
