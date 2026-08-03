<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Feed',             'slug' => 'feed',             'icon' => 'bi-basket',           'color' => '#22c55e'],
            ['name' => 'Medicine',         'slug' => 'medicine',         'icon' => 'bi-capsule',          'color' => '#ef4444'],
            ['name' => 'Veterinary',       'slug' => 'veterinary',       'icon' => 'bi-heart-pulse',      'color' => '#f97316'],
            ['name' => 'Animal Purchase',  'slug' => 'animal-purchase',  'icon' => 'bi-bug',              'color' => '#a855f7'],
            ['name' => 'Equipment',        'slug' => 'equipment',        'icon' => 'bi-tools',            'color' => '#6366f1'],
            ['name' => 'Fuel',             'slug' => 'fuel',             'icon' => 'bi-fuel-pump',        'color' => '#f59e0b'],
            ['name' => 'Electricity',      'slug' => 'electricity',      'icon' => 'bi-lightning-charge', 'color' => '#eab308'],
            ['name' => 'Water',            'slug' => 'water',            'icon' => 'bi-droplet',          'color' => '#06b6d4'],
            ['name' => 'Maintenance',      'slug' => 'maintenance',      'icon' => 'bi-wrench-adjustable', 'color' => '#64748b'],
            ['name' => 'Salary',           'slug' => 'salary',           'icon' => 'bi-cash-stack',       'color' => '#10b981'],
            ['name' => 'Transport',        'slug' => 'transport',        'icon' => 'bi-truck',            'color' => '#3b82f6'],
            ['name' => 'Office Expenses',  'slug' => 'office-expenses',  'icon' => 'bi-building',         'color' => '#8b5cf6'],
            ['name' => 'Miscellaneous',    'slug' => 'miscellaneous',    'icon' => 'bi-three-dots',       'color' => '#94a3b8'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
