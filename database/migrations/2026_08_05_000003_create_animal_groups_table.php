<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animal_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_key')->unique(); // lactating, pregnant, dry, calves, heifers, bulls
            $table->string('name');
            $table->unsignedInteger('head_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_groups');
    }
};
