<?php

namespace App\Services\Guardian;

use App\Models\User;
use App\Notifications\Guardian\GuardianApprovedNotification;
use App\Notifications\Guardian\GuardianRejectedNotification;
use App\Notifications\Guardian\GuardianRequestNotification;
use App\Notifications\Guardian\GuardianRequestSentNotification;
use App\Services\Service;

class GuardianService extends Service
{
    public function sendGuardianRequest(User $ward, User $guardian): void
    {
        // Guardian send info
        $guardian->notify(new GuardianRequestNotification($ward));

        // Ward send info
        $ward->notify(new GuardianRequestSentNotification($guardian));
    }

    public function approveGuardian(User $guardian, User $ward): void
    {
        $ward->update([
            'guardian_id'          => $guardian->id,
            'guardian_approved_at' => now(),
        ]);

        $ward->notify(new GuardianApprovedNotification($guardian));
    }

    public function rejectGuardian(User $guardian, User $ward): void
    {
        if ($ward->guardian_id === $guardian->id) {
            $ward->update(['guardian_id' => null]);
        }

        $ward->update([
            'guardian_id'          => $guardian->id,
            'guardian_approved_at' => now(),
        ]);

        $ward->notify(new GuardianRejectedNotification($guardian));
    }
}
