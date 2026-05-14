<?php

namespace App\Notifications\Guardian;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GuardianApprovedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $guardian,
    ) {
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

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'guardian_approved',
            'title'         => 'Opiekun potwierdzony!',
            'body'          => "{$this->guardian->name} zatwierdził opiekę nad Twoim kontem.",
            'guardian_id'   => $this->guardian->id,
            'guardian_name' => $this->guardian->name,
            'action'        => 'view_account',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
        ];
    }
}
