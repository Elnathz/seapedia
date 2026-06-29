<?php

namespace Database\Seeders;

use App\Enums\DeliveryMethod;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function __construct(
        private readonly \App\Services\TopupService $topups,
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
        private readonly OrderService $orders,
    ) {}

    /**
     * Two more orders from buyer1 against seller1's store, on top of
     * BuyerDemoSeeder's order, so Sprint 5's delivery + overdue flows have
     * something to demo straight after `migrate:fresh --seed`:
     *
     * - one order the seller has already processed, sitting as an
     *   `available` delivery a driver can take immediately;
     * - one paid order whose `sla_due_at` is stamped in the past, so the
     *   very first "advance day" (UI or `seapedia:advance-day`) visibly
     *   refunds it.
     */
    public function run(): void
    {
        $buyer = User::query()->where('email', 'buyer1@seapedia.test')->first();
        $seller = User::query()->where('email', 'seller1@seapedia.test')->first();
        $address = $buyer?->addresses()->first();
        $product = $seller?->store?->products()->first();

        if (! $buyer || ! $seller || ! $address || ! $product) {
            return;
        }

        if ($buyer->wallet->balance <= 0) {
            $this->topups->checkStatus($this->topups->create($buyer, 500_000));
        }

        $this->carts->clear($buyer);
        $this->carts->addItem($buyer, $product, null, 1);
        $takeableOrder = $this->checkout->commit($buyer, $address, DeliveryMethod::Regular);
        $this->orders->processBySeller($takeableOrder, $seller->id);

        $this->carts->clear($buyer);

        $this->carts->addItem($buyer, $product, null, 1);
        $overdueOrder = $this->checkout->commit($buyer, $address, DeliveryMethod::Instant);
        $overdueOrder->update(['sla_due_at' => now()->subDay()]);
    }
}
