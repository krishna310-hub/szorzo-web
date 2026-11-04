<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $message;
    public string $actionUrl;
    public string $status;
    public ?int $leaveId;
    public ?string $leaveType;
    public string $actionType;
    public ?int $userId;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $title,
        string $message,
        string $actionUrl,
        string $status = 'pending',
        ?int $leaveId = null,
        ?string $leaveType = null,
        string $actionType = 'leave_request',
        ?int $userId = null
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->status = $status;
        $this->leaveId = $leaveId;
        $this->leaveType = $leaveType;
        $this->actionType = $actionType;
        $this->userId = $userId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'status' => $this->status,
            'leave_id' => $this->leaveId,
            'leave_type' => $this->leaveType,
            'action_type' => $this->actionType,
            'user_id' => $this->userId ?? ($notifiable->id ?? null),
            'notifiable_id' => $notifiable->id ?? null,
        ];
    }
}
