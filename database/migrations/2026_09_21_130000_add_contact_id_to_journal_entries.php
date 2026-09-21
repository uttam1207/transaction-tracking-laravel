<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('contact_id')
                  ->nullable()
                  ->after('period_id')
                  ->constrained('crm_customers')
                  ->nullOnDelete();
            $table->text('notes')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropColumn(['contact_id', 'notes']);
        });
    }
};
