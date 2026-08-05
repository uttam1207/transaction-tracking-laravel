<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix tables where Azure MySQL 8.0.30+ GIPK (sql_generate_invisible_primary_key)
     * replaced the correct primary key with a hidden `my_row_id` column.
     *
     * Affected tables and their correct primary keys (matched from local DB):
     *   cache                  → PK (`key`)
     *   cache_locks            → PK (`key`)
     *   sessions               → PK (`id`)
     *   password_reset_tokens  → PK (`email`)
     *   role_has_permissions   → PK (`permission_id`, `role_id`)
     *   model_has_permissions  → PK (`permission_id`, `model_id`, `model_type`)
     *   model_has_roles        → PK (`role_id`, `model_id`, `model_type`)
     *   telescope_entries      → PK (`sequence`) AUTO_INCREMENT
     *   telescope_entries_tags → PK (`entry_uuid`, `tag`)
     *   telescope_monitoring   → PK (`tag`)
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

        // --- Simple single-column primary keys ---
        $this->fixSimplePk('cache',                 'key');
        $this->fixSimplePk('cache_locks',           'key');
        $this->fixSimplePk('sessions',              'id');
        $this->fixSimplePk('password_reset_tokens', 'email');
        $this->fixSimplePk('telescope_monitoring',  'tag');

        // --- Composite primary keys ---
        $this->fixCompositePk('role_has_permissions',  ['permission_id', 'role_id']);
        $this->fixCompositePk('model_has_permissions', ['permission_id', 'model_id', 'model_type']);
        $this->fixCompositePk('model_has_roles',       ['role_id', 'model_id', 'model_type']);
        $this->fixCompositePk('telescope_entries_tags',['entry_uuid', 'tag']);

        // --- AUTO_INCREMENT primary key (telescope_entries.sequence) ---
        $this->fixAutoIncrementPk('telescope_entries', 'sequence', 'BIGINT UNSIGNED NOT NULL');

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    // -------------------------------------------------------------------------

    /** Drop the hidden my_row_id GIPK column (or any existing PRIMARY KEY). */
    private function dropGipkIfExists(string $table): void
    {
        $hasGipk = collect(
            DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = 'my_row_id'")
        )->isNotEmpty();

        if ($hasGipk) {
            // Dropping the column also drops the PK constraint it owns
            DB::statement("ALTER TABLE `{$table}` DROP COLUMN `my_row_id`");
        } else {
            // No GIPK column — but PK may still be wrong; drop it manually
            $hasPk = collect(
                DB::select("SHOW INDEX FROM `{$table}` WHERE `Key_name` = 'PRIMARY'")
            )->isNotEmpty();

            if ($hasPk) {
                DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");
            }
        }
    }

    /**
     * Fix a table whose primary key should be a single, non-auto-increment column.
     * e.g. cache.key, sessions.id, password_reset_tokens.email
     */
    private function fixSimplePk(string $table, string $column): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        // Already correct if my_row_id doesn't exist and PK is on the right column
        $hasGipk = collect(
            DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = 'my_row_id'")
        )->isNotEmpty();

        if (! $hasGipk) {
            $pkOnColumn = collect(
                DB::select("SHOW INDEX FROM `{$table}` WHERE `Key_name` = 'PRIMARY' AND `Column_name` = ?", [$column])
            )->isNotEmpty();

            if ($pkOnColumn) {
                return; // already correct
            }
        }

        $this->dropGipkIfExists($table);
        DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$column}`)");
    }

    /**
     * Fix a table whose primary key should be a composite of multiple columns.
     * e.g. role_has_permissions, model_has_roles, telescope_entries_tags
     */
    private function fixCompositePk(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $hasGipk = collect(
            DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = 'my_row_id'")
        )->isNotEmpty();

        if (! $hasGipk) {
            // Check if the first column is already part of a PRIMARY KEY
            $pkExists = collect(
                DB::select("SHOW INDEX FROM `{$table}` WHERE `Key_name` = 'PRIMARY' AND `Column_name` = ?", [$columns[0]])
            )->isNotEmpty();

            if ($pkExists) {
                return; // already correct
            }
        }

        $this->dropGipkIfExists($table);
        $colList = implode('`, `', $columns);
        DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$colList}`)");
    }

    /**
     * Fix a table whose primary key column should be AUTO_INCREMENT.
     * e.g. telescope_entries.sequence
     */
    private function fixAutoIncrementPk(string $table, string $column, string $colDef): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $col = collect(
            DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = ?", [$column])
        )->first();

        if (! $col) {
            return; // column doesn't exist — nothing to fix
        }

        if (stripos($col->Extra, 'auto_increment') !== false) {
            return; // already correct
        }

        $this->dropGipkIfExists($table);
        DB::statement(
            "ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$colDef} AUTO_INCREMENT, ADD PRIMARY KEY (`{$column}`)"
        );
    }

    public function down(): void
    {
        // Intentionally empty — reverting a schema repair is not meaningful
    }
};