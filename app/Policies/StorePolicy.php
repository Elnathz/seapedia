<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    /**
     * A seller may create a store only if they don't already own one (§7
     * stores.user_id UNIQUE — one store per seller).
     */
    public function create(User $user): bool
    {
        return $user->store === null;
    }

    /**
     * Own-only update — cross-seller mutation must be rejected with 403.
     */
    public function update(User $user, Store $store): bool
    {
        return $user->id === $store->user_id;
    }
}
