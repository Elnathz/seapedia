<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Enums\WalletTransactionType;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $carts,
        private readonly WalletService $wallets,
        private readonly OrderService $orders,
        private readonly DiscountService $discounts,
        private readonly DeliveryFeeService $deliveryFees,
        private readonly ClockService $clock,
    ) {}

    /**
     * The §5.2 money breakdown for the buyer's current cart, without
     * mutating anything — used to render the checkout summary before commit.
     *
     * @return array<string, mixed>
     */
    public function preview(
        User $user,
        DeliveryMethod $deliveryMethod,
        ?string $promoCode = null,
        ?string $voucherCode = null,
        ?Address $address = null,
    ): array {
        $cart = $this->carts->summary($user);
        $subtotal = $this->cartSubtotal($cart);

        $discount = $this->discounts->resolve($promoCode, $voucherCode, $subtotal);

        $taxableBase = $subtotal - $discount['discount_total'];
        $taxAmount = (int) round($taxableBase * 0.12);

        // Delivery fee = base(method) + Haversine distance (store origin → this
        // address) + order weight (§5.4). Passing the same address and weight to
        // commit() guarantees the previewed fee equals the amount charged.
        $weightGrams = $this->cartWeightGrams($cart);
        $fee = $this->deliveryFees->breakdown($deliveryMethod, $cart->store, $address, $weightGrams);
        $deliveryFee = $fee['total'];
        $grandTotal = $taxableBase + $taxAmount + $deliveryFee;

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discount['discount_total'],
            'promo' => $discount['promo'] ? ['code' => $discount['promo']->code, 'amount' => $discount['promo_amount']] : null,
            'promo_error' => $discount['promo_error'],
            'voucher' => $discount['voucher'] ? ['code' => $discount['voucher']->code, 'amount' => $discount['voucher_amount']] : null,
            'voucher_error' => $discount['voucher_error'],
            'taxable_base' => $taxableBase,
            'tax_amount' => $taxAmount,
            'delivery_base_fee' => $fee['base_fee'],
            'delivery_distance_km' => $fee['distance_km'],
            'delivery_distance_fee' => $fee['distance_fee'],
            'delivery_weight_grams' => $fee['weight_grams'],
            'delivery_weight_fee' => $fee['weight_fee'],
            'delivery_fee' => $deliveryFee,
            'grand_total' => $grandTotal,
            'balance' => $user->wallet->balance,
            'sufficient_balance' => $user->wallet->balance >= $grandTotal,
        ];
    }

    /**
     * Cart subtotal (§5.2): price from the live variant/product, falling back
     * to the cart snapshot if the product vanished, times quantity. Shared by
     * preview() and the checkout page so the promo picker gates codes against
     * the exact subtotal commit() will re-price under a lock.
     */
    public function cartSubtotal(Cart $cart): int
    {
        return (int) $cart->items->sum(
            fn ($item) => ($item->variant?->price ?? $item->product?->price ?? $item->price_snapshot) * $item->quantity,
        );
    }

    /**
     * Total shippable weight (grams) of the cart: per line, the variant weight
     * when set, else the product weight, times quantity. A null weight counts
     * as 0 so incomplete data never blocks checkout.
     */
    private function cartWeightGrams(Cart $cart): int
    {
        return (int) $cart->items->sum(
            fn ($item) => (($item->variant?->weight ?? $item->product?->weight ?? 0)) * $item->quantity,
        );
    }

    /**
     * Preview → commit, atomically (§6, §5.2, §5.6): lock every product in
     * the cart, re-check stock, decrement it, create the order in Sedang
     * Dikemas, debit the buyer, credit the seller, clear the cart. Any
     * failure rolls back the whole thing — no partial side effects.
     */
    public function commit(
        User $user,
        Address $address,
        DeliveryMethod $deliveryMethod,
        ?string $promoCode = null,
        ?string $voucherCode = null,
    ): Order {
        return DB::transaction(function () use ($user, $address, $deliveryMethod, $promoCode, $voucherCode) {
            $cart = $this->carts->summary($user);

            if ($cart->items->isEmpty() || $cart->store_id === null) {
                throw ValidationException::withMessages([
                    'cart' => [__('Your cart is empty.')],
                ]);
            }

            // §MULTI-ROLE CONFLICT GUARD 1 — defense-in-depth (checkout layer)
            // CartService already rejects own-store products at add-time, but a
            // determined actor could POST directly to /buyer/checkout with a
            // manipulated cart state. We re-check here, inside the locked
            // transaction, so the API surface is equally hardened.
            if ($cart->store !== null && $cart->store->user_id === $user->id) {
                throw ValidationException::withMessages([
                    'cart' => [__('Anda tidak dapat checkout produk dari toko Anda sendiri.')],
                ]);
            }

            // Locked in ascending product_id order (not cart-item order) so
            // two concurrent multi-product checkouts can never deadlock by
            // acquiring the same two row locks in opposite order.
            $lockedProducts = [];
            $lockedVariants = [];
            foreach ($cart->items->sortBy('product_id') as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);

                if (! $product || $product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => [__('Insufficient stock for :product (:stock left).', [
                            'product' => $product?->name ?? (string) $item->product_id,
                            'stock' => $product?->stock ?? 0,
                        ])],
                    ]);
                }

                if ($item->product_variant_id) {
                    $variant = ProductVariant::query()->lockForUpdate()->find($item->product_variant_id);
                    if (! $variant || $variant->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'stock' => [__('Insufficient stock for variant :variant (:stock left).', [
                                'variant' => $variant?->name ?? (string) $item->product_variant_id,
                                'stock' => $variant?->stock ?? 0,
                            ])],
                        ]);
                    }
                    $lockedVariants[$item->id] = $variant;
                }

                $lockedProducts[$item->id] = $product;
            }

            $subtotal = 0;
            $weightGrams = 0;
            $orderItemsData = [];

            foreach ($cart->items as $item) {
                $product = $lockedProducts[$item->id];
                $variant = $lockedVariants[$item->id] ?? null;
                $price = $variant ? $variant->price : $product->price;
                $lineSubtotal = $price * $item->quantity;
                $subtotal += $lineSubtotal;
                $weightGrams += ($variant?->weight ?? $product->weight ?? 0) * $item->quantity;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name_snapshot' => $product->name,
                    'product_variant_name_snapshot' => $variant?->name,
                    'price_snapshot' => $price,
                    'quantity' => $item->quantity,
                    'line_subtotal' => $lineSubtotal,
                ];
            }

            // Voucher row is locked + used_count incremented here (§6), only
            // on a successful eligibility check — any failure below rolls
            // the increment back with the rest of the transaction.
            $discount = $this->discounts->applyAtCommit($promoCode, $voucherCode, $subtotal);

            if ($promoCode && $discount['promo_error']) {
                throw ValidationException::withMessages(['promo_code' => [$discount['promo_error']]]);
            }

            if ($voucherCode && $discount['voucher_error']) {
                throw ValidationException::withMessages(['voucher_code' => [$discount['voucher_error']]]);
            }

            $discountTotal = $discount['discount_total'];
            $taxableBase = $subtotal - $discountTotal;
            $taxAmount = (int) round($taxableBase * 0.12);
            // Same fee formula as preview(): base + Haversine distance (store
            // origin → this address) + order weight, so the charge matches the quote.
            $deliveryFee = $this->deliveryFees->fee($deliveryMethod, $cart->store, $address, $weightGrams);
            $grandTotal = $taxableBase + $taxAmount + $deliveryFee;

            foreach ($lockedProducts as $cartItemId => $product) {
                $quantity = $cart->items->firstWhere('id', $cartItemId)->quantity;
                $product->decrement('stock', $quantity);
                if (isset($lockedVariants[$cartItemId])) {
                    $lockedVariants[$cartItemId]->decrement('stock', $quantity);
                }
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
                'promo_id' => $discount['promo']?->id,
                'voucher_id' => $discount['voucher']?->id,
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

            // Escrow (Sprint 5 Decision 3): the seller is NOT paid here. The
            // buyer's payment is held by the platform; `seller_income_amount`
            // (= taxableBase) is only released to the seller's wallet inside
            // DeliveryService::complete(), when the order reaches Pesanan
            // Selesai. An order that never completes never pays the seller —
            // this is what lets OverdueService::sweep() skip a seller reversal.
            $this->carts->clear($user);

            return $order->load('items');
        });
    }
}
