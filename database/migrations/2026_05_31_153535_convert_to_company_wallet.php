<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isMysql = DB::connection()->getDriverName() === 'mysql';

        if ($isMysql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        // Clear all per-user wallet data
        DB::table('wallet_transactions')->truncate();
        DB::table('wallets')->truncate();

        if ($isMysql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::statement('ALTER TABLE wallets DROP FOREIGN KEY wallets_user_id_foreign');
            DB::statement('ALTER TABLE wallets DROP INDEX wallets_user_id_unique');
            DB::statement('ALTER TABLE wallets MODIFY user_id BIGINT UNSIGNED NULL DEFAULT NULL');
        } else {
            // SQLite: recreate wallets table with nullable user_id (no FK/unique constraints)
            Schema::drop('wallets');
            Schema::create('wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->decimal('balance', 15, 2)->default(0.00);
                $table->string('currency', 10)->default('INR');
                $table->string('status', 20)->default('active');
                $table->timestamps();
            });
        }

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

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE wallets MODIFY user_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE wallets ADD UNIQUE INDEX wallets_user_id_unique (user_id)');
            DB::statement('ALTER TABLE wallets ADD CONSTRAINT wallets_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        }
    }
};
