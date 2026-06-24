<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * A buyer may only view their own orders (cross-buyer access → 403).
     * Seller access to their store's orders is scoped at the query level
     * in `SellerOrderController` (read-only list, no per-order detail yet).
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id;
    }
}
