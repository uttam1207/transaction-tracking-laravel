<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('tags');
            $table->string('recurrence_type', 20)->nullable()->after('is_recurring'); // daily, weekly, monthly
            $table->date('recurring_ends_at')->nullable()->after('recurrence_type');
            $table->unsignedBigInteger('parent_task_id')->nullable()->after('recurring_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'recurrence_type', 'recurring_ends_at', 'parent_task_id']);
        });
    }
};
