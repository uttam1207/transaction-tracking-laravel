<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\MilkEntry;
use Illuminate\Database\Seeder;

class MilkEntrySeeder extends Seeder
{
    /**
     * Seed 30 days of milk data demonstrating all 3 entry modes:
     *  Day 1–10  : Per-Animal entries (each active milking animal, both shifts)
     *  Day 11–20 : Per-Shed entries  (one aggregate per shed per shift)
     *  Day 21–30 : Entire-Farm entries (one combined entry per shift)
     */
    public function run(): void
    {
        MilkEntry::truncate();

        // Active milking animals (not Dry)
        $animals = Animal::where('status', 'Active')
            ->where('pregnancy_status', '!=', 'Dry')
            ->orderBy('shed_number')
            ->orderBy('tag_number')
            ->get();

        // Distinct sheds from milking animals
        $sheds = $animals->pluck('shed_number')->unique()->values();

        $adminId = \App\Models\User::where('role', 'super_admin')->value('id') ?? 1;

        $shifts = ['Morning', 'Evening'];

        // ── Phase 1: Days 1–10 → Per Animal ──────────────────────────────────
        for ($day = 10; $day >= 1; $day--) {
            $date = now()->subDays($day)->toDateString();

            foreach ($shifts as $shift) {
                foreach ($animals as $animal) {
                    $fat = $this->randFat($animal->shed_number);
                    $qty = $this->perAnimalQty($shift);

                    MilkEntry::create([
                        'date'            => $date,
                        'shift'           => $shift,
                        'entry_type'      => 'per_animal',
                        'animal_id'       => $animal->id,
                        'shed_number'     => null,
                        'quantity_liters' => $qty,
                        'fat_percentage'  => $fat,
                        'snf_percentage'  => $this->snfFromFat($fat),
                        'clr_value'       => $this->clrFromFat($fat),
                        'quality_rating'  => $this->gradeFromFat($fat),
                        'rejected_liters' => $this->maybeRejected($qty),
                        'recorded_by'     => $adminId,
                    ]);
                }
            }
        }

        // ── Phase 2: Days 11–20 → Per Shed ───────────────────────────────────
        for ($day = 20; $day >= 11; $day--) {
            $date = now()->subDays($day)->toDateString();

            foreach ($shifts as $shift) {
                foreach ($sheds as $shed) {
                    $animalCount = $animals->where('shed_number', $shed)->count();
                    $fat = $this->randFat($shed);
                    $qty = round($animalCount * $this->perAnimalQty($shift), 1);

                    MilkEntry::create([
                        'date'            => $date,
                        'shift'           => $shift,
                        'entry_type'      => 'per_shed',
                        'animal_id'       => null,
                        'shed_number'     => $shed,
                        'quantity_liters' => $qty,
                        'fat_percentage'  => $fat,
                        'snf_percentage'  => $this->snfFromFat($fat),
                        'clr_value'       => $this->clrFromFat($fat),
                        'quality_rating'  => $this->gradeFromFat($fat),
                        'rejected_liters' => $this->maybeRejected($qty),
                        'recorded_by'     => $adminId,
                    ]);
                }
            }
        }

        // ── Phase 3: Days 21–30 → Entire Farm ────────────────────────────────
        for ($day = 30; $day >= 21; $day--) {
            $date = now()->subDays($day)->toDateString();

            foreach ($shifts as $shift) {
                $fat = round(mt_rand(750, 820) / 100, 2);   // 7.50–8.20
                $qty = round($animals->count() * $this->perAnimalQty($shift), 1);

                MilkEntry::create([
                    'date'            => $date,
                    'shift'           => $shift,
                    'entry_type'      => 'entire_farm',
                    'animal_id'       => null,
                    'shed_number'     => null,
                    'quantity_liters' => $qty,
                    'fat_percentage'  => $fat,
                    'snf_percentage'  => $this->snfFromFat($fat),
                    'clr_value'       => $this->clrFromFat($fat),
                    'quality_rating'  => $this->gradeFromFat($fat),
                    'rejected_liters' => $this->maybeRejected($qty),
                    'recorded_by'     => $adminId,
                ]);
            }
        }

        $total = MilkEntry::count();
        $this->command->info("✓ {$total} milk entries seeded (10d per-animal + 10d per-shed + 10d entire-farm)");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Per-animal yield: morning slightly more than evening */
    private function perAnimalQty(string $shift): float
    {
        return $shift === 'Morning'
            ? round(mt_rand(60, 130) / 10, 1)   // 6.0–13.0 L
            : round(mt_rand(45, 100) / 10, 1);  // 4.5–10.0 L
    }

    /** Fat % realistic for buffalo (slightly higher in Shed 1) */
    private function randFat(string $shed): float
    {
        return $shed === 'Shed No. 1'
            ? round(mt_rand(780, 860) / 100, 2)  // 7.80–8.60
            : round(mt_rand(730, 820) / 100, 2); // 7.30–8.20
    }

    /** SNF correlates with fat (typical: SNF ≈ Fat × 0.7 + 3.8) */
    private function snfFromFat(float $fat): float
    {
        return round($fat * 0.7 + 3.8 + (mt_rand(-10, 10) / 100), 2);
    }

    /** CLR value correlates inversely with fat (approx formula) */
    private function clrFromFat(float $fat): float
    {
        return round(32 - ($fat - 6) * 1.5 + (mt_rand(-5, 5) / 10), 2);
    }

    /** Grade from fat % */
    private function gradeFromFat(float $fat): string
    {
        if ($fat >= 8.0)  return 'Grade A+';
        if ($fat >= 7.0)  return 'Grade A';
        return 'Grade B';
    }

    /** Small chance of rejected liters */
    private function maybeRejected(float $qty): float
    {
        if (mt_rand(1, 10) > 8) {  // ~20% chance
            return round(mt_rand(1, (int)($qty * 5)) / 10, 1);
        }
        return 0;
    }
}
