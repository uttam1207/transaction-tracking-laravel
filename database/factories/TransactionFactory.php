<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 50000);
        $fee    = 0;

        return [
            'category'       => 'payment',
            'type'           => 'credit',
            'amount'         => $amount,
            'fee'            => $fee,
            'net_amount'     => $amount - $fee,
            'currency'       => 'INR',
            'status'         => 'pending',
            'payment_method' => 'bank_transfer',
            'sender_name'    => fake()->name(),
            'sender_mobile'  => '98' . fake()->numerify('########'),
            'receiver_name'  => 'AS Dairy Farm',
            'description'    => fake()->sentence(),
            'risk_score'     => fake()->numberBetween(0, 30),
            'is_flagged'     => false,
            'is_refunded'    => false,
            'processed_at'   => now(),
        ];
    }

    // ── State helpers ─────────────────────────────────────────────────────────

    public function success(): static
    {
        return $this->state(['status' => 'success']);
    }

    public function credit(): static
    {
        return $this->state(['type' => 'credit']);
    }

    public function debit(): static
    {
        return $this->state(['type' => 'debit']);
    }

    public function salesCategory(string $category = 'Milk Sales'): static
    {
        return $this->state(['category' => $category]);
    }

    public function milkSales(): static
    {
        // Only sets category; call ->credit() explicitly when you also need type=credit
        return $this->state(['category' => 'Milk Sales']);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function withAmount(float $amount): static
    {
        return $this->state([
            'amount'     => $amount,
            'net_amount' => $amount,
            'fee'        => 0,
        ]);
    }
}
