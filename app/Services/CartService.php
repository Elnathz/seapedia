<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function resolveOrCreateFor(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Add a product to the buyer's cart. One cart holds items from a single
     * store only (§5.8): the first item sets the store, and adding from a
     * different store is rejected with a 422 unless `$replaceStore` is set,
     * in which case the cart is cleared first (the UI's "Clear & add").
     */
    public function addItem(User $user, Product $product, int $quantity, bool $replaceStore = false): CartItem
    {
        return DB::transaction(function () use ($user, $product, $quantity, $replaceStore) {
            $cart = $this->resolveOrCreateFor($user);

            if ($cart->store_id !== null && $cart->store_id !== $product->store_id) {
                if (! $replaceStore) {
                    throw ValidationException::withMessages([
                        'store' => [__(
                            'Cart contains items from :store. Clear the cart to add from another store?',
                            ['store' => $cart->store->name],
                        )],
                    ]);
                }

                $cart->items()->delete();
                $cart->update(['store_id' => null]);
            }

            if ($cart->store_id === null) {
                $cart->update(['store_id' => $product->store_id]);
            }

            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                $item->update([
                    'quantity' => $item->quantity + $quantity,
                    'price_snapshot' => $product->price,
                ]);

                return $item->refresh();
            }

            return CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price_snapshot' => $product->price,
            ]);
        });
    }

    /**
     * Ownership is enforced by `CartItemPolicy` in the controller before
     * this is called.
     */
    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $item->update(['quantity' => $quantity]);

        return $item->refresh();
    }

    public function removeItem(CartItem $item): void
    {
        $cart = $item->cart;
        $item->delete();

        // An empty cart frees its store lock so the next add can start fresh
        // from any store (§5.8).
        if ($cart->items()->doesntExist()) {
            $cart->update(['store_id' => null]);
        }
    }

    public function clear(User $user): void
    {
        $cart = $this->resolveOrCreateFor($user);
        $cart->items()->delete();
        $cart->update(['store_id' => null]);
    }

    public function summary(User $user): Cart
    {
        return Cart::query()
            ->with([
                'store:id,name,slug',
                'items.product:id,name,slug,image_path,price,stock,store_id',
            ])
            ->firstOrCreate(['user_id' => $user->id]);
    }
}
