<?php

namespace App\Policies;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    /**
     * A driver may preview an unclaimed job (to decide whether to take it)
     * or view the job they're assigned to. Another driver's taken/completed
     * job is none of this driver's business (§L7B cross-user job access).
     */
    public function view(User $user, Delivery $delivery): bool
    {
        return $delivery->status === DeliveryStatus::Available || $user->id === $delivery->driver_id;
    }

    /**
     * §MULTI-ROLE CONFLICT GUARD 2 & 3 — courier == seller / courier == buyer
     *
     * Any driver may take an unclaimed job in principle, BUT a driver who
     * also owns the seller role for the store that fulfilled the order, OR
     * is the same user who placed the order as a buyer, must be blocked.
     * Both conditions represent a user benefiting financially from both sides
     * of a single transaction (earning + income, or earning from own payment).
     *
     * The race-condition guard (two drivers, one job) is still enforced inside
     * DeliveryService::take() under a row lock — this policy is the
     * authorization gate that runs first.
     */
    public function take(User $user, Delivery $delivery): bool
    {
        // Eager-load the order + store relationship if not already loaded.
        $order = $delivery->order ?? $delivery->load('order.store')->order;

        // Guard 2: driver == seller owner of this order's store
        if ($order->store->user_id === $user->id) {
            return false;
        }

        // Guard 3: driver == buyer who placed this order
        if ($order->buyer_id === $user->id) {
            return false;
        }

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
