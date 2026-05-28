<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function send(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        array $data = [],
        ?string $link = null
    ): AppNotification {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'data'    => $data,
            'link'    => $link,
            'icon'    => $this->getIconForType($type),
        ]);

        // Broadcast real-time in-app notification
        try {
            broadcast(new \App\Events\NotificationSent($notification))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Could not broadcast notification: ' . $e->getMessage());
        }

        return $notification;
    }

    public function sendToRole(string $role, string $title, string $message, string $type = 'info', array $data = []): void
    {
        User::whereHas('roles', fn($q) => $q->where('name', $role))
            ->get()
            ->each(fn($user) => $this->send($user, $title, $message, $type, $data));
    }

    public function sendFraudAlert(User $user, array $alertData): void
    {
        $this->send(
            $user,
            'Fraud Alert Detected',
            "Transaction {$alertData['transaction_id']} flagged — risk score {$alertData['risk_score']}",
            'fraud', $alertData,
            '/admin/fraud-alerts'
        );
    }

    public function sendAttendanceReminder(User $user): void
    {
        $this->send($user, 'Attendance Reminder', 'Please mark your attendance for today.', 'warning', [], '/employee/attendance');
    }

    public function sendTaskAssigned(User $user, array $taskData): void
    {
        $this->send($user, 'New Task Assigned', "You have been assigned: {$taskData['title']}", 'info', $taskData, "/employee/tasks/{$taskData['id']}");
    }

    // ─── Slack ──────────────────────────────────────────────────────────────

    public function sendSlack(string $title, string $message, string $color = 'good', array $fields = []): void
    {
        $webhookUrl = config('services.slack.webhook_url');
        if (!$webhookUrl) return;

        $attachment = [
            'color'  => $color,
            'title'  => $title,
            'text'   => $message,
            'footer' => config('app.name'),
            'ts'     => time(),
        ];

        if (!empty($fields)) {
            $attachment['fields'] = array_map(fn($k, $v) => ['title' => $k, 'value' => $v, 'short' => true], array_keys($fields), $fields);
        }

        try {
            Http::post($webhookUrl, ['attachments' => [$attachment]]);
        } catch (\Exception $e) {
            Log::warning('Slack notification failed: ' . $e->getMessage());
        }
    }

    public function sendSlackFraudAlert(string $transactionId, float $riskScore, string $severity): void
    {
        $this->sendSlack(
            '🚨 Fraud Alert Detected',
            "Transaction *{$transactionId}* flagged as *{$severity}* (score: {$riskScore})",
            'danger',
            ['Transaction' => $transactionId, 'Risk Score' => $riskScore, 'Severity' => strtoupper($severity)]
        );
    }

    // ─── Telegram ───────────────────────────────────────────────────────────

    public function sendTelegram(string $message, ?string $chatId = null): void
    {
        $botToken = config('services.telegram.bot_token');
        $chatId   = $chatId ?? config('services.telegram.chat_id');

        if (!$botToken || !$chatId) return;

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::warning('Telegram notification failed: ' . $e->getMessage());
        }
    }

    public function sendTelegramFraudAlert(string $transactionId, float $riskScore, string $severity): void
    {
        $this->sendTelegram(
            "🚨 *Fraud Alert*\n\n" .
            "Transaction: `{$transactionId}`\n" .
            "Severity: *" . strtoupper($severity) . "*\n" .
            "Risk Score: `{$riskScore}`\n" .
            "App: " . config('app.name')
        );
    }

    // ─── helpers ────────────────────────────────────────────────────────────

    private function getIconForType(string $type): string
    {
        return match($type) {
            'success'    => 'check-circle',
            'warning'    => 'exclamation-triangle',
            'danger'     => 'x-circle',
            'fraud'      => 'shield-exclamation',
            'task'       => 'clipboard-list',
            'attendance' => 'clock',
            default      => 'bell',
        };
    }
}
