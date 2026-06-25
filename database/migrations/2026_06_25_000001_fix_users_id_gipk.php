<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair every table where MySQL GIPK has broken the `id` column.
     *
     * On Azure MySQL 8.0.30+ with sql_generate_invisible_primary_key = ON the
     * hidden `my_row_id` column can steal the AUTO_INCREMENT PRIMARY KEY role,
     * leaving `id` as a plain NOT NULL BIGINT with no default — causing insert
     * errors like: "Field 'id' doesn't have a default value".
     *
     * This migration scans every application table that should own its `id`
     * column as AUTO_INCREMENT PRIMARY KEY and repairs any that are broken.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Disable GIPK and FK checks for this session
        try {
            DB::statement('SET sql_generate_invisible_primary_key = OFF');
        } catch (\Throwable $e) {}

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // All tables whose `id` column must be BIGINT UNSIGNED AUTO_INCREMENT PK
        $tables = [
            // Laravel core
            'users',
            'jobs',
            'failed_jobs',
            'personal_access_tokens',

            // HR / org
            'departments',
            'employees',
            'attendance',
            'leaves',
            'tasks',
            'task_comments',
            'projects',
            'work_reports',
            'timesheets',
            'holidays',
            'shifts',

            // Finance
            'transactions',
            'transaction_logs',
            'wallets',
            'wallet_transactions',

            // Fraud & security
            'fraud_alerts',
            'fraud_rules',
            'blacklists',
            'whitelists',
            'login_histories',
            'audit_logs',
            'activity_logs',

            // App
            'app_notifications',
            'settings',
            'documents',
            'service_permissions',

            // Spatie permission (bigIncrements = same underlying type)
            'permissions',
            'roles',

            // Q&A
            'questions',
            'answers',
        ];

        foreach ($tables as $table) {
            $this->repairTable($table);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function repairTable(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        // Check whether id already has AUTO_INCREMENT — if so, nothing to do
        $col = collect(DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = 'id'"))->first();

        if (! $col) {
            return; // table has no `id` column at all
        }

        if (stripos($col->Extra, 'auto_increment') !== false) {
            return; // already correct
        }

        // `id` exists but lacks AUTO_INCREMENT — GIPK has broken this table.
        // Drop the invisible my_row_id GIPK column (this also removes its PK).
        $hasGipk = collect(
            DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = 'my_row_id'")
        )->isNotEmpty();

        if ($hasGipk) {
            DB::statement("ALTER TABLE `{$table}` DROP COLUMN `my_row_id`");
        } else {
            // No GIPK column, but id still lacks AUTO_INCREMENT — drop PK manually
            $hasPk = collect(
                DB::select("SHOW INDEX FROM `{$table}` WHERE `Key_name` = 'PRIMARY'")
            )->isNotEmpty();

            if ($hasPk) {
                DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");
            }
        }

        // Restore id as the proper AUTO_INCREMENT PRIMARY KEY
        DB::statement(
            "ALTER TABLE `{$table}` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)"
        );
    }

    public function down(): void
    {
        // Intentionally empty — reverting a schema repair is not meaningful
    }
};
