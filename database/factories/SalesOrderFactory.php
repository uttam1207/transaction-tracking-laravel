<?php

namespace Database\Factories;

use App\Models\SaleItemType;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    protected static int $seq = 1;

    public function definition(): array
    {
        $total = fake()->randomFloat(2, 100, 20000);

        return [
            'invoice_number' => 'TEST/' . now()->format('y') . '/' . str_pad(static::$seq++, 4, '0', STR_PAD_LEFT),
            'item_type'      => 'Milk Sales',
            'sale_date'      => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'quantity'       => fake()->randomFloat(2, 1, 500),
            'rate'           => fake()->randomFloat(2, 40, 120),
            'total_amount'   => $total,
            'amount_paid'    => $total,    // Paid by default
            'payment_status' => 'Paid',
        ];
    }

    // ── State helpers ─────────────────────────────────────────────────────────

    public function pending(): static
    {
        return $this->state(function (array $attrs) {
            return [
                'payment_status' => 'Pending',
                'amount_paid'    => 0,
            ];
        });
    }

    public function partial(float $paid): static
    {
        return $this->state(function (array $attrs) use ($paid) {
            return [
                'payment_status' => 'Partial',
                'amount_paid'    => $paid,
            ];
        });
    }

    public function unbilled(): static
    {
        return $this->state([
            'payment_status' => 'Unbilled',
            'amount_paid'    => 0,
        ]);
    }

    public function withTotal(float $total): static
    {
        return $this->state(function (array $attrs) use ($total) {
            // Honour any payment status already set by a preceding state call.
            // If status is Paid (default) we also set amount_paid = total.
            $paid = ($attrs['payment_status'] ?? 'Paid') === 'Paid' ? $total : ($attrs['amount_paid'] ?? 0);
            return [
                'total_amount' => $total,
                'amount_paid'  => $paid,
                'rate'         => $total,
            ];
        });
    }

    public function withItemType(string $type): static
    {
        return $this->state(['item_type' => $type]);
    }
}
