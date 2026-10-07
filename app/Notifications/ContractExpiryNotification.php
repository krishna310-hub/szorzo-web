<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractExpiryNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $message;
    public string $actionUrl;
    public string $status;
    public string $contractableType;
    public int $contractableId;
    public ?string $contractToDate;
    public int $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $title,
        string $message,
        string $actionUrl,
        string $status = 'warning',
        string $contractableType = 'employee',
        int $contractableId = 0,
        ?string $contractToDate = null,
        int $daysLeft = 0
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->status = $status;
        $this->contractableType = $contractableType;
        $this->contractableId = $contractableId;
        $this->contractToDate = $contractToDate;
        $this->daysLeft = $daysLeft;
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
            'contractable_type' => $this->contractableType,
            'contractable_id' => $this->contractableId,
            'contract_to_date' => $this->contractToDate,
            'days_left' => $this->daysLeft,
        ];
    }
}
