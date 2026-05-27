<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending')->after('avatar');
            $table->enum('role', ['super_admin', 'admin', 'manager', 'employee', 'auditor', 'viewer'])->default('employee')->after('status');
            $table->unsignedBigInteger('department_id')->nullable()->after('role');
            $table->string('timezone')->default('UTC')->after('department_id');
            $table->string('language')->default('en')->after('timezone');
            $table->string('theme')->default('light')->after('language');
            $table->boolean('two_factor_enabled')->default(false)->after('theme');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->string('otp_code')->nullable()->after('two_factor_recovery_codes');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->string('phone_verified_at')->nullable()->after('otp_expires_at');
            $table->timestamp('last_login_at')->nullable()->after('phone_verified_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->boolean('is_online')->default(false)->after('last_login_ip');
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'phone', 'avatar', 'status', 'role', 'department_id',
                'timezone', 'language', 'theme', 'two_factor_enabled', 'two_factor_secret',
                'two_factor_recovery_codes', 'otp_code', 'otp_expires_at', 'phone_verified_at',
                'last_login_at', 'last_login_ip', 'is_online', 'deleted_at'
            ]);
        });
    }
};
