<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * A seller may only manage products that belong to their own store
     * (own-only via `product.store.user_id` — cross-seller mutation → 403).
     */
    public function update(User $user, Product $product): bool
    {
        return $user->id === $product->store->user_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->id === $product->store->user_id;
    }
}
