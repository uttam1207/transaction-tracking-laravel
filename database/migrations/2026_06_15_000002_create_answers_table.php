<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
           $table->unsignedBigInteger('question_id');
$table->unsignedBigInteger('user_id');

$table->foreign('question_id')
    ->references('my_row_id')
    ->on('questions')
    ->onDelete('cascade');

$table->foreign('user_id')
    ->references('my_row_id')
    ->on('users')
    ->onDelete('cascade');
            $table->text('body');
            $table->boolean('is_accepted')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
