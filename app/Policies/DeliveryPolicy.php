<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    /**
     * Any driver may take an unclaimed job — ownership isn't established
     * until `take()` assigns it (enforced there, under a row lock).
     */
    public function take(User $user, Delivery $delivery): bool
    {
        return true;
    }

    /**
     * Only the driver who took the job may complete it (§5.6).
     */
    public function complete(User $user, Delivery $delivery): bool
    {
        return $user->id === $delivery->driver_id;
    }
}
