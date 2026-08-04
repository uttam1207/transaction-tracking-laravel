<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breeding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            $table->date('heat_date');
            $table->date('ai_date')->nullable();
            $table->string('bull_semen_code')->nullable();
            $table->date('pregnancy_check_date')->nullable();
            $table->boolean('is_pregnant')->nullable();
            $table->date('expected_calving_date')->nullable();
            $table->date('actual_calving_date')->nullable();
            $table->string('calf_tag_number')->nullable();
            $table->enum('status', ['Heat Detected', 'AI Done', 'Confirmed Pregnant', 'Calved', 'Repeat Breeder'])->default('Heat Detected');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breeding_records');
    }
};
