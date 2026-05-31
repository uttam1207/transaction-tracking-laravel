<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Disable FK checks so we can truncate freely
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear all per-user wallet data
        DB::table('wallet_transactions')->truncate();
        DB::table('wallets')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Drop FK & unique constraint, make user_id nullable
        DB::statement('ALTER TABLE wallets DROP FOREIGN KEY wallets_user_id_foreign');
        DB::statement('ALTER TABLE wallets DROP INDEX wallets_user_id_unique');
        DB::statement('ALTER TABLE wallets MODIFY user_id BIGINT UNSIGNED NULL DEFAULT NULL');

        // Create the single company wallet
        DB::table('wallets')->insert([
            'user_id'    => null,
            'balance'    => 0.00,
            'currency'   => 'INR',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('wallet_transactions')->truncate();
        DB::table('wallets')->truncate();

        DB::statement('ALTER TABLE wallets MODIFY user_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE wallets ADD UNIQUE INDEX wallets_user_id_unique (user_id)');
        DB::statement('ALTER TABLE wallets ADD CONSTRAINT wallets_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }
};
