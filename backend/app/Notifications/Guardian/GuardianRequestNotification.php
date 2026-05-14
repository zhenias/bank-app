<?php

namespace App\Notifications\Guardian;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GuardianRequestNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $ward,
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
            'type'               => 'guardian_request',
            'title'              => 'Nowa prośba o opiekę',
            'body'               => "{$this->ward->name} prosi o potwierdzenie opieki.",
            'ward_id'            => $this->ward->id,
            'ward_name'          => $this->ward->name,
            'ward_email'         => $this->ward->email,
            'action'             => 'approve_guardian',
            'action_url'         => '/guardian/approve/' . $this->ward->id,
            'ward_date_of_birth' => $this->ward->date_of_birth?->format('Y-m-d'),
            'ward_age'           => $this->ward->date_of_birth?->age,
            'ward_email'         => $this->ward->email,
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
