<?php

namespace Database\Factories;

use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChartOfAccount>
 */
class ChartOfAccountFactory extends Factory
{
    protected static int $codeSeq = 9000;

    public function definition(): array
    {
        return [
            'code'                 => (string) (static::$codeSeq++),
            'name'                 => fake()->unique()->words(3, true),
            'type'                 => fake()->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']),
            'sub_type'             => null,
            'description'          => null,
            'is_active'            => true,
            'allow_direct_posting' => true,
        ];
    }

    // ── Preset account helpers ────────────────────────────────────────────────

    public function bankAccount(): static
    {
        return $this->state([
            'code'     => '1010',
            'name'     => 'Bank Account',
            'type'     => 'asset',
            'sub_type' => 'current_asset',
        ]);
    }

    public function revenueAccount(): static
    {
        return $this->state([
            'code'     => '4000',
            'name'     => 'Milk Sales Revenue',
            'type'     => 'revenue',
            'sub_type' => 'operating_revenue',
        ]);
    }

    public function cogsAccount(): static
    {
        return $this->state([
            'code'     => '5000',
            'name'     => 'Cost of Milk Sold',
            'type'     => 'expense',
            'sub_type' => 'cost_of_sales',
        ]);
    }

    public function expenseAccount(): static
    {
        return $this->state([
            'code'     => '6000',
            'name'     => 'Operating Expenses',
            'type'     => 'expense',
            'sub_type' => 'operating_expense',
        ]);
    }

    public function arAccount(): static
    {
        return $this->state([
            'code'     => '1100',
            'name'     => 'Accounts Receivable',
            'type'     => 'asset',
            'sub_type' => 'current_asset',
        ]);
    }
}
