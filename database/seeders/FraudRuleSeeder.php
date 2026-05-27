<?php

namespace Database\Seeders;

use App\Models\FraudRule;
use Illuminate\Database\Seeder;

class FraudRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name' => 'High Amount Alert',
                'code' => 'HIGH_AMOUNT',
                'description' => 'Flag transactions above $10,000',
                'type' => 'amount',
                'conditions' => ['threshold' => 10000],
                'action' => 'flag',
                'risk_score' => 30,
                'severity' => 'medium',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Critical Amount Alert',
                'code' => 'CRITICAL_AMOUNT',
                'description' => 'Block transactions above $50,000',
                'type' => 'amount',
                'conditions' => ['threshold' => 50000],
                'action' => 'review',
                'risk_score' => 60,
                'severity' => 'high',
                'priority' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Velocity Check',
                'code' => 'VELOCITY_CHECK',
                'description' => 'Flag if more than 5 transactions in 1 hour',
                'type' => 'velocity',
                'conditions' => ['max_transactions' => 5, 'time_window_minutes' => 60],
                'action' => 'flag',
                'risk_score' => 40,
                'severity' => 'medium',
                'priority' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Duplicate Transaction',
                'code' => 'DUPLICATE_TX',
                'description' => 'Flag duplicate transactions within 10 minutes',
                'type' => 'duplicate',
                'conditions' => ['time_window_minutes' => 10],
                'action' => 'flag',
                'risk_score' => 70,
                'severity' => 'high',
                'priority' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Geo Restriction',
                'code' => 'GEO_RESTRICTION',
                'description' => 'Block transactions from restricted countries',
                'type' => 'geo',
                'conditions' => ['blocked_countries' => ['KP', 'IR', 'SY', 'CU']],
                'action' => 'block',
                'risk_score' => 90,
                'severity' => 'critical',
                'priority' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            FraudRule::firstOrCreate(['code' => $rule['code']], $rule);
        }

        $this->command->info('Fraud rules seeded!');
    }
}
