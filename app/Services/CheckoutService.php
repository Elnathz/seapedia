<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Enums\WalletTransactionType;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $carts,
        private readonly WalletService $wallets,
        private readonly OrderService $orders,
        private readonly ClockService $clock,
    ) {}

    /**
     * The §5.2 money breakdown for the buyer's current cart, without
     * mutating anything — used to render the checkout summary before commit.
     *
     * @return array<string, mixed>
     */
    public function preview(User $user, DeliveryMethod $deliveryMethod): array
    {
        $cart = $this->carts->summary($user);

        $subtotal = $cart->items->sum(
            fn ($item) => $item->price_snapshot * $item->quantity,
        );

        // Discount is a zero placeholder this sprint — the math/summary
        // slots exist now so Sprint 4 can fill in a real value with no
        // checkout rework.
        $discountTotal = 0;
        $taxableBase = $subtotal - $discountTotal;
        $taxAmount = (int) round($taxableBase * 0.12);
        $deliveryFee = $deliveryMethod->fee();
        $grandTotal = $taxableBase + $taxAmount + $deliveryFee;

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'taxable_base' => $taxableBase,
            'tax_amount' => $taxAmount,
            'delivery_fee' => $deliveryFee,
            'grand_total' => $grandTotal,
            'balance' => $user->wallet->balance,
            'sufficient_balance' => $user->wallet->balance >= $grandTotal,
        ];
    }

    /**
     * Preview → commit, atomically (§6, §5.2, §5.6): lock every product in
     * the cart, re-check stock, decrement it, create the order in Sedang
     * Dikemas, debit the buyer, credit the seller, clear the cart. Any
     * failure rolls back the whole thing — no partial side effects.
     */
    public function commit(User $user, Address $address, DeliveryMethod $deliveryMethod): Order
    {
        return DB::transaction(function () use ($user, $address, $deliveryMethod) {
            $cart = $this->carts->summary($user);

            if ($cart->items->isEmpty() || $cart->store_id === null) {
                throw ValidationException::withMessages([
                    'cart' => [__('Your cart is empty.')],
                ]);
            }

            $lockedProducts = [];
            foreach ($cart->items as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);

                if (! $product || $product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => [__('Insufficient stock for :product (:stock left).', [
                            'product' => $product?->name ?? (string) $item->product_id,
                            'stock' => $product?->stock ?? 0,
                        ])],
                    ]);
                }

                $lockedProducts[$item->id] = $product;
            }

            $subtotal = 0;
            $orderItemsData = [];

            foreach ($cart->items as $item) {
                $product = $lockedProducts[$item->id];
                $lineSubtotal = $product->price * $item->quantity;
                $subtotal += $lineSubtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'price_snapshot' => $product->price,
                    'quantity' => $item->quantity,
                    'line_subtotal' => $lineSubtotal,
                ];
            }

            $discountTotal = 0;
            $taxableBase = $subtotal - $discountTotal;
            $taxAmount = (int) round($taxableBase * 0.12);
            $deliveryFee = $deliveryMethod->fee();
            $grandTotal = $taxableBase + $taxAmount + $deliveryFee;

            foreach ($lockedProducts as $cartItemId => $product) {
                $quantity = $cart->items->firstWhere('id', $cartItemId)->quantity;
                $product->decrement('stock', $quantity);
            }

            $now = $this->clock->now();

            $order = $this->orders->createFromCheckout([
                'store_id' => $cart->store_id,
                'ship_recipient' => $address->recipient_name,
                'ship_phone' => $address->phone,
                'ship_address' => $address->full_address,
                'delivery_method' => $deliveryMethod,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'delivery_fee' => $deliveryFee,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'seller_income_amount' => $taxableBase,
                'created_sim_at' => $now,
                'sla_due_at' => $now->copy()->addDays($deliveryMethod->slaTicks()),
                'paid_at' => $now,
            ], $orderItemsData, $user);

            // Debit the buyer; throws (422, insufficient balance) if short
            // — everything above (order, items, stock) rolls back with it.
            $this->wallets->debit(
                $user->wallet,
                $grandTotal,
                WalletTransactionType::Payment,
                'order',
                $order->id,
            );

            // Seller income is settled instantly (§5.1b "spendable... instant
            // settlement"), excluding tax and delivery fee — it isn't seller
            // revenue. §5.9's overdue reversal assumes this already happened.
            // (Cart's `store` relation is column-scoped for display, so the
            // seller's wallet is resolved fresh here instead.)
            $sellerWallet = Store::query()->find($cart->store_id)->user->wallet;

            $this->wallets->credit(
                $sellerWallet,
                $taxableBase,
                WalletTransactionType::Income,
                'order',
                $order->id,
            );

            $this->carts->clear($user);

            return $order->load('items');
        });
    }
}
