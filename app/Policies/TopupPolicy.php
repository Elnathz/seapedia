<?php

namespace App\Policies;

use App\Models\Topup;
use App\Models\User;

class TopupPolicy
{
    /**
     * A buyer may only poll/view their own top-up (cross-user access → 403).
     */
    public function view(User $user, Topup $topup): bool
    {
        return $user->id === $topup->wallet->user_id;
    }
}
