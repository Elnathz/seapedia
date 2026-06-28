<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Enums\WalletTransactionType;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
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
    ): array {
        $cart = $this->carts->summary($user);

        // Price from the live product (fallback to the cart snapshot if the
        // product vanished) so the previewed total matches what commit()
        // actually charges — commit re-prices from the live product under a
        // lock, and the buyer must be charged exactly what the summary shows.
        $subtotal = $cart->items->sum(
            fn ($item) => ($item->variant?->price ?? $item->product?->price ?? $item->price_snapshot) * $item->quantity,
        );

        $discount = $this->discounts->resolve($promoCode, $voucherCode, $subtotal);

        $taxableBase = $subtotal - $discount['discount_total'];
        $taxAmount = (int) round($taxableBase * 0.12);
        $deliveryFee = $deliveryMethod->fee();
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
                    $variant = \App\Models\ProductVariant::query()->lockForUpdate()->find($item->product_variant_id);
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
            $orderItemsData = [];

            foreach ($cart->items as $item) {
                $product = $lockedProducts[$item->id];
                $variant = $lockedVariants[$item->id] ?? null;
                $price = $variant ? $variant->price : $product->price;
                $lineSubtotal = $price * $item->quantity;
                $subtotal += $lineSubtotal;

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
            $deliveryFee = $deliveryMethod->fee();
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
