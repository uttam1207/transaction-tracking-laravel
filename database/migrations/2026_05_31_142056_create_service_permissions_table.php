<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('service_key')->unique();
            $table->string('service_name');
            $table->string('description')->nullable();
            $table->string('icon')->default('grid');
            $table->json('allowed_roles');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_permissions');
    }
};
