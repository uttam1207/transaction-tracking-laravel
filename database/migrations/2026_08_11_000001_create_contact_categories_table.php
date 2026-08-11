<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('icon', 60)->default('bi-people');
            $table->string('color', 30)->default('#6366f1');
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default categories
        $defaults = [
            ['name' => 'Milk Vendors',                      'icon' => 'bi-truck',            'color' => '#059669', 'description' => 'Suppliers who deliver or sell milk to the farm'],
            ['name' => 'Direct Milk Clients',               'icon' => 'bi-bag-check',         'color' => '#0891b2', 'description' => 'Customers who buy milk directly from the farm'],
            ['name' => 'Construction Vendors',              'icon' => 'bi-cone-striped',      'color' => '#d97706', 'description' => 'Contractors and suppliers for construction and civil work'],
            ['name' => 'Veterinarian',                      'icon' => 'bi-heart-pulse',       'color' => '#dc2626', 'description' => 'Vets, clinics and animal health service providers'],
            ['name' => 'Feed Suppliers',                    'icon' => 'bi-basket',            'color' => '#7c3aed', 'description' => 'Animal feed and fodder suppliers'],
            ['name' => 'Equipment & Machinery',             'icon' => 'bi-gear',              'color' => '#374151', 'description' => 'Farm equipment, machinery and spare parts dealers'],
            ['name' => 'Transport & Logistics',             'icon' => 'bi-bus-front',         'color' => '#ea580c', 'description' => 'Transport and delivery service providers'],
            ['name' => 'Government / Regulatory Bodies',    'icon' => 'bi-bank',              'color' => '#1d4ed8', 'description' => 'Govt offices, inspectors and compliance authorities'],
        ];

        foreach ($defaults as $cat) {
            DB::table('contact_categories')->insert(array_merge($cat, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_categories');
    }
};