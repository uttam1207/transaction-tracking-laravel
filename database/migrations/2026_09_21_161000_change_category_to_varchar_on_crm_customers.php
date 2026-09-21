<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change ENUM to VARCHAR(100) so dynamic categories from crm_categories table work
        DB::statement("ALTER TABLE crm_customers MODIFY category VARCHAR(100) NOT NULL DEFAULT 'Milk Buyer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE crm_customers MODIFY category ENUM('Milk Buyer','Animal Buyer','Franchise Lead','Investor','Government Official','Veterinary Doctor') NOT NULL DEFAULT 'Milk Buyer'");
    }
};