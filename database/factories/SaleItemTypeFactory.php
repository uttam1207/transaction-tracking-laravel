<?php

namespace Database\Factories;

use App\Models\SaleItemType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItemType>
 */
class SaleItemTypeFactory extends Factory
{
    protected static int $order = 1;

    public function definition(): array
    {
        return [
            'name'         => fake()->unique()->word() . ' Sales',
            'is_milk_type' => false,
            'sort_order'   => static::$order++,
            'is_active'    => true,
        ];
    }

    public function milkType(): static
    {
        return $this->state(['name' => 'Milk Sales', 'is_milk_type' => true]);
    }

    public function forCategory(string $category): static
    {
        return $this->state(['name' => $category]);
    }
}
