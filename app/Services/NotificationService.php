<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function send(User $user, string $title, string $message, string $type = 'info', array $data = [], ?string $link = null): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'link' => $link,
            'icon' => $this->getIconForType($type),
        ]);

        // Broadcast real-time notification
        try {
            broadcast(new \App\Events\NotificationSent($notification))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Could not broadcast notification: ' . $e->getMessage());
        }

        return $notification;
    }

    public function sendToRole(string $role, string $title, string $message, string $type = 'info', array $data = []): void
    {
        User::where('role', $role)->active()->each(function ($user) use ($title, $message, $type, $data) {
            $this->send($user, $title, $message, $type, $data);
        });
    }

    public function sendFraudAlert(User $user, array $alertData): void
    {
        $this->send(
            $user,
            'Fraud Alert Detected',
            "Transaction {$alertData['transaction_id']} has been flagged with risk score {$alertData['risk_score']}",
            'fraud',
            $alertData,
            route('admin.fraud-alerts.index')
        );
    }

    public function sendAttendanceReminder(User $user): void
    {
        $this->send(
            $user,
            'Attendance Reminder',
            'Please mark your attendance for today.',
            'warning',
            [],
            route('employee.attendance.index')
        );
    }

    public function sendTaskAssigned(User $user, array $taskData): void
    {
        $this->send(
            $user,
            'New Task Assigned',
            "You have been assigned: {$taskData['title']}",
            'info',
            $taskData,
            route('employee.tasks.show', $taskData['id'])
        );
    }

    private function getIconForType(string $type): string
    {
        return match($type) {
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'danger' => 'x-circle',
            'fraud' => 'shield-exclamation',
            'task' => 'clipboard-list',
            'attendance' => 'clock',
            default => 'bell',
        };
    }
}
