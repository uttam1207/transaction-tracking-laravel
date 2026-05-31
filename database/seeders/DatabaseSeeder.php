<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\FraudRuleSeeder;
use Database\Seeders\HolidaySeeder;
use Database\Seeders\TransactionSeeder;
use Database\Seeders\ServicePermissionSeeder;
use Database\Seeders\RoleSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            EmployeeSeeder::class,
            SettingSeeder::class,
            FraudRuleSeeder::class,
            HolidaySeeder::class,
            TransactionSeeder::class,
            ServicePermissionSeeder::class,
            RoleSeeder::class,
        ]);
    }
}
