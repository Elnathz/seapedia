<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    /**
     * A buyer may only manage items in their own cart (cross-user
     * mutation → 403).
     */
    public function update(User $user, CartItem $item): bool
    {
        return $user->id === $item->cart->user_id;
    }

    public function delete(User $user, CartItem $item): bool
    {
        return $user->id === $item->cart->user_id;
    }
}
