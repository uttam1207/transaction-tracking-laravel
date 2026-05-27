<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;
        $holidays = [
            ['name' => "New Year's Day", 'date' => "{$year}-01-01", 'type' => 'national'],
            ['name' => 'Independence Day', 'date' => "{$year}-07-04", 'type' => 'national'],
            ['name' => 'Thanksgiving Day', 'date' => "{$year}-11-28", 'type' => 'national'],
            ['name' => 'Christmas Day', 'date' => "{$year}-12-25", 'type' => 'national'],
            ['name' => "New Year's Eve", 'date' => "{$year}-12-31", 'type' => 'company'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(['date' => $holiday['date']], $holiday);
        }

        $this->command->info('Holidays seeded!');
    }
}
