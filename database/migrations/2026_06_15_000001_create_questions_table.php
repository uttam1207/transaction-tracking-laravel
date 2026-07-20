<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');

$table->foreign('user_id')
      ->references('my_row_id')
      ->on('users')
      ->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->enum('status', ['open', 'resolved', 'closed'])->default('open');
            $table->boolean('is_pinned')->default(false);
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
