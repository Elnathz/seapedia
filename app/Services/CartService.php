<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
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
    public function addItem(User $user, Product $product, ?ProductVariant $variant, int $quantity, bool $replaceStore = false): CartItem
    {
        return DB::transaction(function () use ($user, $product, $variant, $quantity, $replaceStore) {
            $cart = $this->resolveOrCreateFor($user);

            // §MULTI-ROLE CONFLICT GUARD 1 — buyer == seller
            // A user may own both a buyer and a seller role. They must NOT be
            // allowed to purchase from their own store, because that creates
            // circular money (payment → income on the same wallet) and a false
            // audit trail. This is enforced here — the earliest possible point —
            // so the restriction holds even if the caller bypasses the UI and
            // posts directly to the API.
            if ($product->store->user_id === $user->id) {
                throw ValidationException::withMessages([
                    'product' => [__('Anda tidak dapat membeli produk dari toko Anda sendiri.')],
                ]);
            }

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

            $query = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id);

            if ($variant) {
                $query->where('product_variant_id', $variant->id);
            } else {
                $query->whereNull('product_variant_id');
            }

            $item = $query->first();

            $priceSnapshot = $variant ? $variant->price : $product->price;

            if ($item) {
                $item->update([
                    'quantity' => $item->quantity + $quantity,
                    'price_snapshot' => $priceSnapshot,
                ]);

                return $item->refresh();
            }

            return CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $quantity,
                'price_snapshot' => $priceSnapshot,
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
                // Origin lat/lng + item weights feed the distance-based delivery fee (§5.4).
                'store:id,name,slug,province,city,district,village,origin_latitude,origin_longitude',
                'items.product:id,name,slug,image_path,price,stock,weight,store_id',
                'items.variant:id,product_id,name,price,stock,weight',
            ])
            ->firstOrCreate(['user_id' => $user->id]);
    }
}
