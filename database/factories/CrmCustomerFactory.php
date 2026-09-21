<?php

namespace Database\Factories;

use App\Models\CrmCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrmCustomer>
 */
class CrmCustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'                 => fake()->name(),
            'category'             => fake()->randomElement(['Milk Buyer', 'Animal Buyer', 'Franchise Lead', 'Investor']),
            'phone'                => fake()->phoneNumber(),
            'email'                => fake()->unique()->safeEmail(),
            'address'              => fake()->address(),
            'total_business_value' => fake()->randomFloat(2, 0, 50000),
            'status'               => 'Active Customer',
        ];
    }

    public function milkBuyer(): static
    {
        return $this->state(['category' => 'Milk Buyer']);
    }
}
