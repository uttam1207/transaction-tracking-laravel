<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('receiver_mobile', 20)->nullable()->after('receiver_bank');
            $table->string('receiver_company', 255)->nullable()->after('receiver_mobile');
            $table->text('receiver_address')->nullable()->after('receiver_company');
            $table->string('sender_mobile', 20)->nullable()->after('sender_bank');
            $table->string('sender_company', 255)->nullable()->after('sender_mobile');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'receiver_mobile', 'receiver_company', 'receiver_address',
                'sender_mobile', 'sender_company',
            ]);
        });
    }
};
