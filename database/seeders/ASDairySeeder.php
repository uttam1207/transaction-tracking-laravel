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
            ['name' => 'Feed', 'icon' => 'bi-basket', 'color' => '#10b981'],
            ['name' => 'Medicine', 'icon' => 'bi-capsule', 'color' => '#ef4444'],
            ['name' => 'Veterinary', 'icon' => 'bi-heart-pulse', 'color' => '#ec4899'],
            ['name' => 'Animal Purchase', 'icon' => 'bi-cart-plus', 'color' => '#8b5cf6'],
            ['name' => 'Equipment', 'icon' => 'bi-tools', 'color' => '#3b82f6'],
            ['name' => 'Fuel', 'icon' => 'bi-fuel-pump', 'color' => '#f59e0b'],
            ['name' => 'Electricity', 'icon' => 'bi-lightning', 'color' => '#eab308'],
            ['name' => 'Water', 'icon' => 'bi-droplet', 'color' => '#06b6d4'],
            ['name' => 'Maintenance', 'icon' => 'bi-wrench', 'color' => '#64748b'],
            ['name' => 'Salary', 'icon' => 'bi-cash-stack', 'color' => '#14b8a6'],
            ['name' => 'Transport', 'icon' => 'bi-truck', 'color' => '#6366f1'],
            ['name' => 'Office Expenses', 'icon' => 'bi-building', 'color' => '#94a3b8'],
            ['name' => 'Miscellaneous', 'icon' => 'bi-three-dots', 'color' => '#475569'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Predefined Inventory Items & Initial Stock
        $user = User::first() ?? User::create([
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'password' => bcrypt('Admin@123'),
            'role' => 'super_admin',
        ]);

        $feedItems = [
            ['name' => 'Green Fodder', 'category' => 'Feed', 'unit' => 'kg', 'min_stock' => 1000, 'initial_stock' => 3500],
            ['name' => 'Dry Fodder', 'category' => 'Feed', 'unit' => 'kg', 'min_stock' => 500, 'initial_stock' => 1200],
            ['name' => 'Concentrate', 'category' => 'Feed', 'unit' => 'kg', 'min_stock' => 400, 'initial_stock' => 300],
            ['name' => 'Mineral Mixture', 'category' => 'Feed', 'unit' => 'kg', 'min_stock' => 50, 'initial_stock' => 120],
            ['name' => 'Albendazole', 'category' => 'Medicine', 'unit' => 'bottles', 'min_stock' => 10, 'initial_stock' => 45],
            ['name' => 'Milking Machine Cleaner', 'category' => 'Cleaning Material', 'unit' => 'liters', 'min_stock' => 5, 'initial_stock' => 20],
        ];

        foreach ($feedItems as $itemData) {
            $item = InventoryItem::firstOrCreate(
                ['name' => $itemData['name']],
                [
                    'category' => $itemData['category'],
                    'unit' => $itemData['unit'],
                    'min_stock' => $itemData['min_stock'],
                    'is_active' => true,
                ]
            );

            // Record initial stock if not exists
            if ($item->stockMovements()->count() === 0) {
                StockMovement::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'in',
                    'quantity' => $itemData['initial_stock'],
                    'date' => now()->toDateString(),
                    'source_purpose' => 'Initial Stock Record',
                    'issued_to_or_vendor' => 'ASDairy Farm Warehouse',
                    'recorded_by' => $user->id,
                    'remarks' => 'Opening inventory count for ASDairy Anoo Village',
                ]);
            }
        }

        // 3. Animal Groups (Matching specification)
        $groupsData = [
            ['group_key' => 'lactating', 'name' => 'Lactating Buffaloes', 'head_count' => 10],
            ['group_key' => 'pregnant', 'name' => 'Pregnant Animals', 'head_count' => 4],
            ['group_key' => 'dry', 'name' => 'Dry Animals', 'head_count' => 3],
            ['group_key' => 'calves', 'name' => 'Female/Male Calves', 'head_count' => 5],
            ['group_key' => 'heifers', 'name' => 'Heifers', 'head_count' => 0],
            ['group_key' => 'bulls', 'name' => 'Bulls', 'head_count' => 0],
        ];

        foreach ($groupsData as $gd) {
            $group = AnimalGroup::firstOrCreate(
                ['group_key' => $gd['group_key']],
                ['name' => $gd['name'], 'head_count' => $gd['head_count']]
            );

            // 4. Default Feed Plans per group
            $feedAllocations = match ($gd['group_key']) {
                'lactating' => ['Green Fodder' => 30, 'Dry Fodder' => 5, 'Concentrate' => 6, 'Mineral Mixture' => 0.1],
                'pregnant' => ['Green Fodder' => 20, 'Dry Fodder' => 4, 'Concentrate' => 3, 'Mineral Mixture' => 0.05],
                'dry' => ['Green Fodder' => 15, 'Dry Fodder' => 4, 'Concentrate' => 2, 'Mineral Mixture' => 0.05],
                'calves' => ['Green Fodder' => 5, 'Dry Fodder' => 1, 'Concentrate' => 1, 'Mineral Mixture' => 0.02],
                default => ['Green Fodder' => 10, 'Dry Fodder' => 2, 'Concentrate' => 1, 'Mineral Mixture' => 0.02],
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
