<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\FraudRule;
use App\Models\FraudAlert;
use App\Models\Blacklist;
use Illuminate\Support\Facades\Log;

class FraudDetectionService
{
    private int $totalRiskScore = 0;
    private array $triggeredRules = [];

    public function analyze(Transaction $transaction): array
    {
        $this->totalRiskScore = 0;
        $this->triggeredRules = [];

        $rules = FraudRule::active()->get();

        foreach ($rules as $rule) {
            $this->evaluateRule($rule, $transaction);
        }

        // Additional built-in checks
        $this->checkBlacklist($transaction);
        $this->checkHighAmount($transaction);
        $this->checkDuplicate($transaction);
        $this->checkVelocity($transaction);

        $riskScore = min($this->totalRiskScore, 100);

        return [
            'risk_score' => $riskScore,
            'is_flagged' => $riskScore >= 50,
            'triggered_rules' => $this->triggeredRules,
            'recommendation' => $this->getRecommendation($riskScore),
        ];
    }

    private function evaluateRule(FraudRule $rule, Transaction $transaction): void
    {
        $conditions = $rule->conditions;
        $matched = false;

        switch ($rule->type) {
            case 'amount':
                $matched = $this->checkAmountRule($conditions, $transaction);
                break;
            case 'velocity':
                $matched = $this->checkVelocityRule($conditions, $transaction);
                break;
            case 'blacklist':
                $matched = $this->checkBlacklistRule($conditions, $transaction);
                break;
            case 'geo':
                $matched = $this->checkGeoRule($conditions, $transaction);
                break;
            case 'duplicate':
                $matched = $this->checkDuplicateRule($conditions, $transaction);
                break;
        }

        if ($matched) {
            $this->totalRiskScore += $rule->risk_score;
            $this->triggeredRules[] = [
                'rule' => $rule->name,
                'type' => $rule->type,
                'severity' => $rule->severity,
                'score' => $rule->risk_score,
            ];
        }
    }

    private function checkAmountRule(array $conditions, Transaction $transaction): bool
    {
        $threshold = $conditions['threshold'] ?? 10000;
        return $transaction->amount >= $threshold;
    }

    private function checkVelocityRule(array $conditions, Transaction $transaction): bool
    {
        $maxTransactions = $conditions['max_transactions'] ?? 5;
        $timeWindowMinutes = $conditions['time_window_minutes'] ?? 60;

        $count = Transaction::where('user_id', $transaction->user_id)
            ->where('created_at', '>=', now()->subMinutes($timeWindowMinutes))
            ->count();

        return $count > $maxTransactions;
    }

    private function checkBlacklistRule(array $conditions, Transaction $transaction): bool
    {
        return Blacklist::isBlacklisted('ip', $transaction->ip_address ?? '')
            || Blacklist::isBlacklisted('account', $transaction->sender_account ?? '')
            || Blacklist::isBlacklisted('account', $transaction->receiver_account ?? '');
    }

    private function checkGeoRule(array $conditions, Transaction $transaction): bool
    {
        $blockedCountries = $conditions['blocked_countries'] ?? [];
        return in_array($transaction->country, $blockedCountries);
    }

    private function checkDuplicateRule(array $conditions, Transaction $transaction): bool
    {
        $timeWindowMinutes = $conditions['time_window_minutes'] ?? 5;

        return Transaction::where('user_id', $transaction->user_id)
            ->where('amount', $transaction->amount)
            ->where('receiver_account', $transaction->receiver_account)
            ->where('created_at', '>=', now()->subMinutes($timeWindowMinutes))
            ->where('id', '!=', $transaction->id)
            ->exists();
    }

    private function checkBlacklist(Transaction $transaction): void
    {
        if (Blacklist::isBlacklisted('ip', $transaction->ip_address ?? '')) {
            $this->totalRiskScore += 80;
            $this->triggeredRules[] = ['rule' => 'Blacklisted IP', 'type' => 'blacklist', 'severity' => 'critical', 'score' => 80];
        }

        if (Blacklist::isBlacklisted('country', $transaction->country ?? '')) {
            $this->totalRiskScore += 60;
            $this->triggeredRules[] = ['rule' => 'Blacklisted Country', 'type' => 'blacklist', 'severity' => 'high', 'score' => 60];
        }
    }

    private function checkHighAmount(Transaction $transaction): void
    {
        if ($transaction->amount >= 50000) {
            $this->totalRiskScore += 40;
            $this->triggeredRules[] = ['rule' => 'High Amount Transaction', 'type' => 'amount', 'severity' => 'high', 'score' => 40];
        } elseif ($transaction->amount >= 10000) {
            $this->totalRiskScore += 20;
            $this->triggeredRules[] = ['rule' => 'Large Amount Transaction', 'type' => 'amount', 'severity' => 'medium', 'score' => 20];
        }
    }

    private function checkDuplicate(Transaction $transaction): void
    {
        $isDuplicate = Transaction::where('user_id', $transaction->user_id)
            ->where('amount', $transaction->amount)
            ->where('receiver_account', $transaction->receiver_account)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->where('id', '!=', $transaction->id)
            ->exists();

        if ($isDuplicate) {
            $this->totalRiskScore += 70;
            $this->triggeredRules[] = ['rule' => 'Duplicate Transaction', 'type' => 'duplicate', 'severity' => 'high', 'score' => 70];
        }
    }

    private function checkVelocity(Transaction $transaction): void
    {
        $count = Transaction::where('user_id', $transaction->user_id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($count > 10) {
            $this->totalRiskScore += 60;
            $this->triggeredRules[] = ['rule' => 'High Velocity (>10/hr)', 'type' => 'velocity', 'severity' => 'high', 'score' => 60];
        } elseif ($count > 5) {
            $this->totalRiskScore += 30;
            $this->triggeredRules[] = ['rule' => 'Elevated Velocity (>5/hr)', 'type' => 'velocity', 'severity' => 'medium', 'score' => 30];
        }
    }

    private function getRecommendation(int $score): string
    {
        return match(true) {
            $score >= 80 => 'block',
            $score >= 60 => 'review',
            $score >= 40 => 'flag',
            default => 'allow',
        };
    }

    public function createAlert(Transaction $transaction, array $analysisResult): void
    {
        foreach ($analysisResult['triggered_rules'] as $rule) {
            FraudAlert::create([
                'transaction_id' => $transaction->id,
                'alert_type' => $rule['type'],
                'severity' => $rule['severity'],
                'risk_score' => $rule['score'],
                'description' => $rule['rule'],
                'metadata' => $analysisResult,
                'status' => 'open',
            ]);
        }
    }
}
