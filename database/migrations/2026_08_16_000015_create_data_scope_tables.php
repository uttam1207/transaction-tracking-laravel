<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Data Scope Engine ───────────────────────────────────────────────
        // Associates a (Spatie) role with a scope for a specific permission.
        // When a user checks 'task.view', the engine resolves their role's
        // scope and applies the appropriate query filter automatically.
        Schema::create('role_data_scopes', function (Blueprint $table) {
            $table->id();
            // References spatie roles.id
            $table->unsignedBigInteger('role_id');
            // MODULE.ACTION e.g. 'employee.view', 'task.create'
            $table->string('permission', 100);
            $table->enum('scope_type', [
                'GLOBAL',       // see everything (super_admin)
                'COMPANY',      // see all within company
                'BRANCH',       // see all within branch
                'DEPARTMENT',   // see own department records
                'TEAM',         // see own team records
                'PROJECT',      // see records linked to their projects
                'OWN',          // see only their own records
            ])->default('OWN');
            // Which column resolves the scope context.
            // e.g. 'department_id', 'team_id', 'branch_id', 'user_id'
            $table->string('scope_column', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->unique(['role_id', 'permission'], 'role_scope_permission_unique');
            $table->index('permission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_data_scopes');
    }
};
