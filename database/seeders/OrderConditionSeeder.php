<?php

namespace Database\Seeders;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Models\Address;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\DeliveryService;
use App\Services\OrderService;
use App\Services\OverdueService;
use App\Services\TopupService;
use Illuminate\Database\Seeder;

/**
 * Seeds demo-ready orders in every status so every surface (buyer, seller,
 * driver, admin) has something to show without any user interaction.
 *
 * Status coverage (via real service calls):
 *   1. Sedang Dikemas   — buyer1 → seller1, fresh checkout
 *   2. Menunggu Pengirim — buyer1 → seller1, seller processed (available jobs)
 *   4. Pesanan Selesai  — driver1's 2 completed jobs (earnings visible)
 *   3. Sedang Dikirim   — driver1's active job (taken after completions)
 *   5. Dikembalikan     — one overdue order past sla_due_at (sweep pending)
 *
 * Drivers: driver1 has 2 completed + 1 active; driver2 has 2 completed;
 * 2 more "Menunggu Pengirim" orders remain available for the "take job" demo.
 */
class OrderConditionSeeder extends Seeder
{
    public function __construct(
        private readonly TopupService $topups,
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
        private readonly OrderService $orders,
        private readonly DeliveryService $deliveries,
        private readonly OverdueService $overdue,
    ) {}

    public function run(): void
    {
        // Zero processing delay so the seeder completes instantly.
        config(['payment.topup.processing_seconds' => 0]);

        $buyer1 = User::query()->where('username', 'buyer1')->first();
        $seller1 = User::query()->where('username', 'seller1')->first();
        $driver1 = User::query()->where('username', 'driver1')->first();
        $driver2 = User::query()->where('username', 'driver2')->first();

        if (! $buyer1 || ! $seller1 || ! $driver1 || ! $driver2) {
            return;
        }

        $address1 = $buyer1->addresses()->first();
        if (! $address1) {
            $this->seedAddress($buyer1);
            $address1 = $buyer1->addresses()->first();
        }

        if (! $address1) {
            return;
        }

        $this->topupIfEmpty($buyer1, 500_000);

        // Collect products as plain array to avoid Eloquent collection quirks.
        $products = ($seller1->store?->products ?? collect())->all();
        if (count($products) < 2) {
            return;
        }

        $p0 = $products[0];
        $p1 = $products[1];
        $p2 = $products[2] ?? $p1;

        // ----------------------------------------------------------------
        // 1. Sedang Dikemas — fresh checkout, no seller action yet.
        // ----------------------------------------------------------------
        $this->carts->clear($buyer1);
        $this->carts->addItem($buyer1, $p0, 2);
        $this->checkout->commit($buyer1, $address1, DeliveryMethod::Regular);

        // ----------------------------------------------------------------
        // 2. Menunggu Pengirim — seller processed, driver hasn't taken.
        //    Leave 2 of these as Available so "take job" is demoable.
        // ----------------------------------------------------------------
        // Order A: p0 × 1.
        $this->carts->clear($buyer1);
        $this->carts->addItem($buyer1, $p0, 1);
        $orderA = $this->checkout->commit($buyer1, $address1, DeliveryMethod::Regular);
        $this->orders->processBySeller($orderA, $seller1->id);

        // Order B: p1 × 1.
        $this->carts->clear($buyer1);
        $this->carts->addItem($buyer1, $p1, 1);
        $orderB = $this->checkout->commit($buyer1, $address1, DeliveryMethod::Regular);
        $this->orders->processBySeller($orderB, $seller1->id);

        // Order C: p2 × 1 (driver1 will complete this).
        $this->carts->clear($buyer1);
        $this->carts->addItem($buyer1, $p2, 1);
        $orderC = $this->checkout->commit($buyer1, $address1, DeliveryMethod::Regular);
        $this->orders->processBySeller($orderC, $seller1->id);

        // ----------------------------------------------------------------
        // 4. Pesanan Selesai — driver2's 2 completed jobs + driver1's 2.
        //    Done BEFORE section 3's active job so driver1 has no active job
        //    when they need to take a second one later.
        // ----------------------------------------------------------------
        // Driver2: complete 2 available jobs.
        $availableJobs = Delivery::query()
            ->where('status', DeliveryStatus::Available)
            ->limit(2)
            ->get();

        foreach ($availableJobs as $delivery) {
            $this->topupIfEmpty($driver2, 50_000);
            $this->deliveries->take($delivery, $driver2);
            $delivery->refresh();
            $this->deliveries->complete($delivery, $driver2);
        }

        // Driver1: complete order C (their first completed job).
        $driver1Delivery = Delivery::query()
            ->where('order_id', $orderC->id)
            ->where('status', DeliveryStatus::Available)
            ->first();

        if ($driver1Delivery) {
            $this->topupIfEmpty($driver1, 50_000);
            $this->deliveries->take($driver1Delivery, $driver1);
            $driver1Delivery->refresh();
            $this->deliveries->complete($driver1Delivery, $driver1);
        }

        // ----------------------------------------------------------------
        // 3. Sedang Dikirim — driver1's active job.
        //    Any stale Taken job for driver1 was completed above, so the
        //    "one active job" constraint allows a fresh take() now.
        // ----------------------------------------------------------------
        $this->carts->clear($buyer1);
        $this->carts->addItem($buyer1, $p0, 1);
        $activeOrder = $this->checkout->commit($buyer1, $address1, DeliveryMethod::Regular);
        $this->orders->processBySeller($activeOrder, $seller1->id);

        $activeDelivery = Delivery::query()
            ->where('order_id', $activeOrder->id)
            ->where('status', DeliveryStatus::Available)
            ->first();

        if ($activeDelivery) {
            $this->topupIfEmpty($driver1, 50_000);
            $this->deliveries->take($activeDelivery, $driver1);
        }

        // ----------------------------------------------------------------
        // 5. Dikembalikan — sla_due_at in the past, pending overdue sweep.
        // ----------------------------------------------------------------
        $this->carts->clear($buyer1);
        $this->carts->addItem($buyer1, $p0, 1);
        $overdueOrder = $this->checkout->commit($buyer1, $address1, DeliveryMethod::Regular);
        $overdueOrder->update(['sla_due_at' => now()->subDay()]);
    }

    /**
     * Helper: seed a default address if none exists.
     */
    private function seedAddress(User $user): void
    {
        $user->addresses()->create([
            'recipient_name' => $user->name,
            'phone' => $user->phone ?? '081234567890',
            'full_address' => 'Jl. Demo No. 1, Semarang',
            'is_default' => true,
        ]);
    }

    /**
     * Helper: credit the wallet if it's currently empty (no ledger entry).
     */
    private function topupIfEmpty(User $user, int $amount): void
    {
        if ($user->wallet->balance <= 0) {
            $this->topups->checkStatus($this->topups->create($user, $amount));
        }
    }
}
