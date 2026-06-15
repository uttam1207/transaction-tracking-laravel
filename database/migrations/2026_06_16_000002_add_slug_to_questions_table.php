<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        // Generate unique slugs for any existing questions
        $questions = DB::table('questions')->orderBy('id')->get(['id', 'title']);
        foreach ($questions as $q) {
            $base = Str::slug($q->title);
            $slug = $base;
            $count = 1;
            while (DB::table('questions')->where('slug', $slug)->exists()) {
                $slug = $base . '-' . $count++;
            }
            DB::table('questions')->where('id', $q->id)->update(['slug' => $slug]);
        }

        // Now enforce unique constraint
        Schema::table('questions', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
