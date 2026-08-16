<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── employees — lifecycle fields ───────────────────────────────────────
        Schema::table('employees', function (Blueprint $table) {
            $table->date('probation_end_date')->nullable()->after('leaving_date');
            $table->unsignedSmallInteger('notice_period_days')->default(30)->after('probation_end_date');
            $table->string('profile_photo')->nullable()->after('notice_period_days');
        });

        // ── leaves — normalise leave type FK ──────────────────────────────────
        Schema::table('leaves', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_type_id')->nullable()->after('employee_id');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['leave_type_id']);
            $table->dropColumn('leave_type_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['probation_end_date', 'notice_period_days', 'profile_photo']);
        });
    }
};
