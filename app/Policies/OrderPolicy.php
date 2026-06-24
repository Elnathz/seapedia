<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * A buyer may view their own orders; a seller may view orders placed
     * against their own store. Cross-user access → 403 either way.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id || $user->id === $order->store->user_id;
    }

    /**
     * Only the owning seller may process an incoming order (§5.6) — never
     * the buyer, and never a different seller's store.
     */
    public function process(User $user, Order $order): bool
    {
        return $user->id === $order->store->user_id;
    }
}
