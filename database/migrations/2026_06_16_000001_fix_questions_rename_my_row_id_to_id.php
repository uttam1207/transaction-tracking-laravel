<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix two issues caused by MySQL 8 GIPK auto-generating 'my_row_id':
     *  1. questions table: if my_row_id exists instead of id, recreate it properly.
     *  2. answers table: create it if it doesn't exist (was dropped in a previous attempt).
     */
    public function up(): void
    {
        // ── Fix questions table if GIPK is still present ──────────────────────
        if (Schema::hasColumn('questions', 'my_row_id') && ! Schema::hasColumn('questions', 'id')) {
            $existing = DB::table('questions')->get([
                'user_id', 'title', 'body', 'status', 'is_pinned',
                'views', 'created_at', 'updated_at', 'deleted_at',
            ]);

            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            Schema::dropIfExists('answers');
            Schema::dropIfExists('questions');

            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('title');
                $table->text('body');
                $table->enum('status', ['open', 'resolved', 'closed'])->default('open');
                $table->boolean('is_pinned')->default(false);
                $table->unsignedBigInteger('views')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            foreach ($existing as $q) {
                DB::table('questions')->insert((array) $q);
            }
        }

        // ── Create answers table if missing ────────────────────────────────────
        if (! Schema::hasTable('answers')) {
            Schema::create('answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->text('body');
                $table->boolean('is_accepted')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
