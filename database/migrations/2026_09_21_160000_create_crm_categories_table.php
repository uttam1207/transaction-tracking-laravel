<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_categories', function (Blueprint $table) {
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
            ['name' => 'Milk Buyer',                       'icon' => 'bi-droplet-fill',    'color' => '#3b82f6', 'description' => 'Customers who purchase milk'],
            ['name' => 'Animal Buyer',                     'icon' => 'bi-bag-check',       'color' => '#10b981', 'description' => 'Customers who purchase animals'],
            ['name' => 'Franchise Lead',                   'icon' => 'bi-shop',            'color' => '#f59e0b', 'description' => 'Potential franchise partners'],
            ['name' => 'Investor',                         'icon' => 'bi-cash-coin',       'color' => '#6366f1', 'description' => 'Investors and stakeholders'],
            ['name' => 'Government Official',              'icon' => 'bi-building',        'color' => '#64748b', 'description' => 'Government or regulatory contacts'],
            ['name' => 'Veterinary Doctor',                'icon' => 'bi-heart-pulse',     'color' => '#ef4444', 'description' => 'Veterinary professionals'],
            ['name' => 'Registered Business - Regular',    'icon' => 'bi-patch-check-fill','color' => '#0ea5e9', 'description' => 'Business that is registered under GST'],
            ['name' => 'Registered Business - Composition','icon' => 'bi-patch-check',     'color' => '#8b5cf6', 'description' => 'Business that is registered under the Composition Scheme in GST'],
            ['name' => 'Unregistered Business',            'icon' => 'bi-building-x',      'color' => '#f97316', 'description' => 'Business that has not been registered under GST'],
            ['name' => 'Consumer',                         'icon' => 'bi-person-fill',     'color' => '#14b8a6', 'description' => 'A customer who is a regular consumer'],
        ];

        foreach ($defaults as $row) {
            DB::table('crm_categories')->insert(array_merge($row, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_categories');
    }
};