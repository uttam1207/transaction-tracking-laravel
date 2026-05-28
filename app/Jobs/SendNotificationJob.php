<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public User $user,
        public string $title,
        public string $message,
        public string $type = 'info',
        public array $data = [],
        public ?string $link = null
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $notificationService->send($this->user, $this->title, $this->message, $this->type, $this->data, $this->link);
    }
}
