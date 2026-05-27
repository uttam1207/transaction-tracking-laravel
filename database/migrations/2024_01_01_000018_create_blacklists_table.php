<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ip', 'email', 'account', 'device', 'country']);
            $table->string('value');
            $table->text('reason');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'value']);
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('whitelists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ip', 'email', 'account', 'device', 'country']);
            $table->string('value');
            $table->text('reason');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'value']);
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklists');
        Schema::dropIfExists('whitelists');
    }
};
