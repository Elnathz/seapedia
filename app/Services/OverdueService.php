<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\WalletTransactionType;
use App\Models\Order;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class OverdueService
{
    private const array ELIGIBLE_STATUSES = [
        OrderStatus::SedangDikemas,
        OrderStatus::MenungguPengirim,
        OrderStatus::SedangDikirim,
    ];

    public function __construct(
        private readonly OrderService $orders,
        private readonly WalletService $wallets,
        private readonly ClockService $clock,
    ) {}

    /**
     * Idempotent auto-refund pass (§5.9, escrow-adapted): every non-final
     * order past its SLA is refunded to the buyer and its stock restored.
     * No seller reversal — under escrow the seller is only ever paid on
     * Pesanan Selesai, so an overdue order never paid out in the first
     * place. Voucher `used_count` is left untouched (§5.3).
     *
     * @return array{refunded_count: int}
     */
    public function sweep(): array
    {
        $now = $this->clock->now();

        $orderIds = Order::query()
            ->whereIn('status', self::ELIGIBLE_STATUSES)
            ->whereNull('refunded_at')
            ->where('sla_due_at', '<', $now)
            ->pluck('id');

        $refunded = 0;

        foreach ($orderIds as $orderId) {
            if ($this->refundOne($orderId, $now)) {
                $refunded++;
            }
        }

        return ['refunded_count' => $refunded];
    }

    /**
     * How many orders are currently overdue and eligible for the next
     * sweep — used by the admin monitoring dashboard, read-only.
     */
    public function eligibleCount(): int
    {
        return Order::query()
            ->whereIn('status', self::ELIGIBLE_STATUSES)
            ->whereNull('refunded_at')
            ->where('sla_due_at', '<', $this->clock->now())
            ->count();
    }

    private function refundOne(int $orderId, CarbonImmutable $now): bool
    {
        return DB::transaction(function () use ($orderId, $now) {
            $order = Order::query()->lockForUpdate()->with('items')->findOrFail($orderId);

            // Re-check inside the lock: a concurrent sweep or a driver
            // completing the order between the select above and this lock
            // may have already finalized it.
            if ($order->refunded_at !== null || ! in_array($order->status, self::ELIGIBLE_STATUSES, true)) {
                return false;
            }

            $productIds = $order->items->pluck('product_id')->filter()->sort()->values();

            foreach ($productIds as $productId) {
                $product = Product::query()->lockForUpdate()->find($productId);
                $quantity = $order->items->firstWhere('product_id', $productId)->quantity;
                $product?->increment('stock', $quantity);
            }

            $this->wallets->credit(
                $order->buyer->wallet,
                $order->grand_total,
                WalletTransactionType::Refund,
                'order',
                $order->id,
            );

            $order->update(['refunded_at' => $now]);

            $this->orders->transition($order, OrderStatus::Dikembalikan, null, 'Auto-refund: SLA overdue');

            return true;
        });
    }
}
