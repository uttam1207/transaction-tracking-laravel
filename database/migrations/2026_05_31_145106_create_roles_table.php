<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Extends the existing Spatie roles table with UI-management columns
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
            $table->text('description')->nullable()->after('display_name');
            $table->string('color', 20)->default('#6366f1')->after('description');
            $table->string('icon', 50)->default('person-badge')->after('color');
            $table->boolean('is_system')->default(false)->after('icon');
            $table->boolean('is_active')->default(true)->after('is_system');
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'display_name', 'description', 'color', 'icon',
                'is_system', 'is_active', 'sort_order',
            ]);
        });
    }
};
