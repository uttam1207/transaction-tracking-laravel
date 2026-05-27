<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['group' => 'general', 'key' => 'app_name', 'value' => 'Transaction Monitor', 'type' => 'string', 'label' => 'Application Name', 'is_public' => true],
            ['group' => 'general', 'key' => 'app_tagline', 'value' => 'Enterprise Transaction Monitoring', 'type' => 'string', 'label' => 'Tagline', 'is_public' => true],
            ['group' => 'general', 'key' => 'app_timezone', 'value' => 'UTC', 'type' => 'string', 'label' => 'Timezone'],
            ['group' => 'general', 'key' => 'app_currency', 'value' => 'USD', 'type' => 'string', 'label' => 'Default Currency', 'is_public' => true],
            ['group' => 'general', 'key' => 'app_currency_symbol', 'value' => '$', 'type' => 'string', 'label' => 'Currency Symbol', 'is_public' => true],
            ['group' => 'general', 'key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'label' => 'Maintenance Mode'],
            // Notification
            ['group' => 'notification', 'key' => 'email_notifications', 'value' => '1', 'type' => 'boolean', 'label' => 'Email Notifications'],
            ['group' => 'notification', 'key' => 'fraud_alert_email', 'value' => '1', 'type' => 'boolean', 'label' => 'Fraud Alert Emails'],
            ['group' => 'notification', 'key' => 'attendance_reminders', 'value' => '1', 'type' => 'boolean', 'label' => 'Attendance Reminders'],
            // Security
            ['group' => 'security', 'key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer', 'label' => 'Max Login Attempts'],
            ['group' => 'security', 'key' => 'session_lifetime', 'value' => '120', 'type' => 'integer', 'label' => 'Session Lifetime (minutes)'],
            ['group' => 'security', 'key' => 'require_2fa', 'value' => '0', 'type' => 'boolean', 'label' => 'Require 2FA for Admin'],
            // Fraud
            ['group' => 'fraud', 'key' => 'auto_flag_threshold', 'value' => '50', 'type' => 'integer', 'label' => 'Auto Flag Risk Score Threshold'],
            ['group' => 'fraud', 'key' => 'auto_block_threshold', 'value' => '80', 'type' => 'integer', 'label' => 'Auto Block Risk Score Threshold'],
            ['group' => 'fraud', 'key' => 'high_amount_threshold', 'value' => '10000', 'type' => 'integer', 'label' => 'High Amount Alert Threshold ($)'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], array_merge($setting, ['is_public' => $setting['is_public'] ?? false]));
        }

        $this->command->info('Settings seeded!');
    }
}
