<?php

namespace Database\Seeders;

use App\Models\AnimalGroup;
use App\Models\ExpenseCategory;
use App\Models\FeedPlan;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ASDairySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Predefined Expense Categories
        $categories = [
            ['name' => 'Feed',            'icon' => 'bi-basket',      'color' => '#10b981'],
            ['name' => 'Medicine',        'icon' => 'bi-capsule',     'color' => '#ef4444'],
            ['name' => 'Veterinary',      'icon' => 'bi-heart-pulse', 'color' => '#ec4899'],
            ['name' => 'Animal Purchase', 'icon' => 'bi-cart-plus',   'color' => '#8b5cf6'],
            ['name' => 'Equipment',       'icon' => 'bi-tools',       'color' => '#3b82f6'],
            ['name' => 'Fuel',            'icon' => 'bi-fuel-pump',   'color' => '#f59e0b'],
            ['name' => 'Electricity',     'icon' => 'bi-lightning',   'color' => '#eab308'],
            ['name' => 'Water',           'icon' => 'bi-droplet',     'color' => '#06b6d4'],
            ['name' => 'Maintenance',     'icon' => 'bi-wrench',      'color' => '#64748b'],
            ['name' => 'Salary',          'icon' => 'bi-cash-stack',  'color' => '#14b8a6'],
            ['name' => 'Transport',       'icon' => 'bi-truck',       'color' => '#6366f1'],
            ['name' => 'Office Expenses', 'icon' => 'bi-building',    'color' => '#94a3b8'],
            ['name' => 'Miscellaneous',   'icon' => 'bi-three-dots',  'color' => '#475569'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name'      => $cat['name'],
                    'icon'      => $cat['icon'],
                    'color'     => $cat['color'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Admin user
        $user = User::first() ?? User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@demo.com',
            'password' => bcrypt('Admin@123'),
            'role'     => 'super_admin',
        ]);

        // 3. Inventory Items with correct item_type & unit (updateOrCreate to fix any wrong data)
        $inventoryItems = [
            // --- Feed items ---
            [
                'name'          => 'Green Fodder',
                'category'      => 'Feed',
                'item_type'     => 'Green Fodder',
                'unit'          => 'kg',
                'min_stock'     => 1000,
                'initial_stock' => 3500,
                'remarks'       => 'Fresh green grass / maize silage. Opening stock.',
            ],
            [
                'name'          => 'Dry Fodder',
                'category'      => 'Feed',
                'item_type'     => 'Dry Fodder',
                'unit'          => 'kg',
                'min_stock'     => 500,
                'initial_stock' => 1200,
                'remarks'       => 'Wheat straw / paddy straw. Opening stock.',
            ],
            [
                'name'          => 'Concentrate',
                'category'      => 'Feed',
                'item_type'     => 'Concentrate',
                'unit'          => 'kg',
                'min_stock'     => 400,
                'initial_stock' => 800,
                'remarks'       => 'Dairy concentrate mix. Opening stock.',
            ],
            [
                'name'          => 'Mineral Mixture',
                'category'      => 'Feed',
                'item_type'     => 'Mineral Mixture',
                'unit'          => 'kg',
                'min_stock'     => 20,
                'initial_stock' => 60,
                'remarks'       => 'Calcium & mineral supplement. Opening stock.',
            ],
            [
                'name'          => 'Bran',
                'category'      => 'Feed',
                'item_type'     => 'Dry Fodder',
                'unit'          => 'kg',           // Fixed: was incorrectly set to "100"
                'min_stock'     => 50,
                'initial_stock' => 300,
                'remarks'       => 'Wheat bran supplement. Opening stock.',
            ],
            // --- Medicine ---
            [
                'name'          => 'Albendazole',
                'category'      => 'Medicine',
                'item_type'     => 'Oral Medicine',
                'unit'          => 'bottles',
                'min_stock'     => 10,
                'initial_stock' => 45,
                'remarks'       => 'Deworming medicine. Opening stock.',
            ],
            [
                'name'          => 'Calcium Borogluconate',
                'category'      => 'Medicine',
                'item_type'     => 'Injectable',
                'unit'          => 'bottles',
                'min_stock'     => 5,
                'initial_stock' => 20,
                'remarks'       => 'IV calcium supplement for milk fever. Opening stock.',
            ],
            [
                'name'          => 'Oxytocin',
                'category'      => 'Medicine',
                'item_type'     => 'Injectable',
                'unit'          => 'vials',
                'min_stock'     => 10,
                'initial_stock' => 30,
                'remarks'       => 'Milk let-down / calving aid. Opening stock.',
            ],
            // --- Cleaning ---
            [
                'name'          => 'Milking Machine Cleaner',
                'category'      => 'Cleaning Material',
                'item_type'     => 'General',
                'unit'          => 'liters',
                'min_stock'     => 5,
                'initial_stock' => 20,
                'remarks'       => 'Dairy equipment sanitiser. Opening stock.',
            ],
            [
                'name'          => 'Phenyl Disinfectant',
                'category'      => 'Cleaning Material',
                'item_type'     => 'General',
                'unit'          => 'liters',
                'min_stock'     => 10,
                'initial_stock' => 40,
                'remarks'       => 'Shed & floor disinfectant. Opening stock.',
            ],
        ];

        foreach ($inventoryItems as $itemData) {
            // updateOrCreate so we can correct unit/item_type for any existing record
            $item = InventoryItem::updateOrCreate(
                ['name' => $itemData['name']],
                [
                    'category'  => $itemData['category'],
                    'item_type' => $itemData['item_type'],
                    'unit'      => $itemData['unit'],
                    'min_stock' => $itemData['min_stock'],
                    'is_active' => true,
                ]
            );

            // Record initial stock only if no "Initial Stock Record" movement exists yet
            if (!$item->stockMovements()->where('source_purpose', 'Initial Stock Record')->exists()) {
                StockMovement::create([
                    'inventory_item_id'  => $item->id,
                    'type'               => 'in',
                    'quantity'           => $itemData['initial_stock'],
                    'date'               => now()->toDateString(),
                    'source_purpose'     => 'Initial Stock Record',
                    'issued_to_or_vendor'=> 'ASDairy Farm Warehouse',
                    'recorded_by'        => $user->id,
                    'remarks'            => $itemData['remarks'],
                ]);
            }
        }

        // 4. Animal Groups
        $groupsData = [
            ['group_key' => 'lactating', 'name' => 'Lactating Buffaloes', 'head_count' => 10],
            ['group_key' => 'pregnant',  'name' => 'Pregnant Animals',    'head_count' => 4],
            ['group_key' => 'dry',       'name' => 'Dry Animals',         'head_count' => 3],
            ['group_key' => 'calves',    'name' => 'Female/Male Calves',  'head_count' => 5],
            ['group_key' => 'heifers',   'name' => 'Heifers',             'head_count' => 2],
            ['group_key' => 'bulls',     'name' => 'Bulls',               'head_count' => 1],
        ];

        foreach ($groupsData as $gd) {
            $group = AnimalGroup::updateOrCreate(
                ['group_key' => $gd['group_key']],
                ['name' => $gd['name'], 'head_count' => $gd['head_count']]
            );

            // 5. Feed Plans per group (correct dairy-standard allocations kg/head/day)
            $feedAllocations = match ($gd['group_key']) {
                // High producers — max nutrition
                'lactating' => [
                    'Green Fodder'   => 30.0,   // 30 kg green grass/silage
                    'Dry Fodder'     => 5.0,    // 5 kg wheat/paddy straw
                    'Concentrate'    => 6.0,    // 6 kg dairy concentrate
                    'Bran'           => 1.5,    // 1.5 kg wheat bran supplement
                    'Mineral Mixture'=> 0.10,   // 100 g mineral mix
                ],
                // Pregnant — moderate nutrition, +1 kg concentrate in last 60 days (simplified here)
                'pregnant' => [
                    'Green Fodder'   => 20.0,
                    'Dry Fodder'     => 4.0,
                    'Concentrate'    => 3.0,
                    'Bran'           => 1.0,
                    'Mineral Mixture'=> 0.05,
                ],
                // Dry / non-milking
                'dry' => [
                    'Green Fodder'   => 15.0,
                    'Dry Fodder'     => 5.0,
                    'Concentrate'    => 1.5,
                    'Mineral Mixture'=> 0.05,
                ],
                // Calves (under 6 months — grass + starter)
                'calves' => [
                    'Green Fodder'   => 5.0,
                    'Dry Fodder'     => 1.0,
                    'Concentrate'    => 0.5,
                    'Mineral Mixture'=> 0.02,
                ],
                // Heifers (6 months – first calving)
                'heifers' => [
                    'Green Fodder'   => 12.0,
                    'Dry Fodder'     => 3.0,
                    'Concentrate'    => 1.5,
                    'Mineral Mixture'=> 0.05,
                ],
                // Bulls — maintenance ration
                default => [
                    'Green Fodder'   => 15.0,
                    'Dry Fodder'     => 3.0,
                    'Concentrate'    => 2.0,
                    'Mineral Mixture'=> 0.05,
                ],
            };

            foreach ($feedAllocations as $feedItemName => $qty) {
                FeedPlan::updateOrCreate(
                    ['animal_group_id' => $group->id, 'feed_item_name' => $feedItemName],
                    ['quantity_per_animal_kg' => $qty]
                );
            }
        }
    }
}