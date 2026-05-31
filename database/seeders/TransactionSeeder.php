<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->toArray();
        $categories = ['payment', 'transfer', 'refund', 'deposit', 'withdrawal', 'fee'];
        $statuses = ['pending', 'processing', 'success', 'failed', 'cancelled'];
        $currencies = ['INR'];
        $countries = ['US', 'GB', 'CA', 'DE', 'FR', 'AU'];
        $paymentMethods = ['credit_card', 'debit_card', 'bank_transfer', 'paypal', 'crypto'];

        for ($i = 0; $i < 50; $i++) {
            $amount = rand(10, 50000) + (rand(0, 99) / 100);
            $status = $statuses[array_rand($statuses)];

            Transaction::create([
                'transaction_id' => 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 10)),
                'user_id' => $users[array_rand($users)],
                'category' => $categories[array_rand($categories)],
                'type' => rand(0, 1) ? 'debit' : 'credit',
                'amount' => $amount,
                'currency' => $currencies[array_rand($currencies)],
                'fee' => round($amount * 0.01, 2),
                'net_amount' => round($amount - ($amount * 0.01), 2),
                'status' => $status,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'sender_name' => 'Sender ' . $i,
                'sender_account' => 'ACC' . rand(100000, 999999),
                'receiver_name' => 'Receiver ' . $i,
                'receiver_account' => 'ACC' . rand(100000, 999999),
                'ip_address' => rand(10, 200) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254),
                'country' => $countries[array_rand($countries)],
                'risk_score' => rand(0, 100),
                'is_flagged' => rand(0, 5) === 0,
                'created_at' => now()->subDays(rand(0, 90))->subHours(rand(0, 23)),
            ]);
        }

        $this->command->info('50 sample transactions seeded!');
    }
}
