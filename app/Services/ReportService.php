<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @return array{total_spent: int, order_count: int, breakdown: list<array{status: string, count: int, total: int}>}
     */
    public function buyerSpending(User $buyer): array
    {
        // Dikembalikan orders were refunded (§5.9) — they never count as
        // realized spend, so they're excluded from both the total and the
        // breakdown rather than just netted to zero.
        $orders = Order::query()
            ->where('buyer_id', $buyer->id)
            ->where('status', '!=', OrderStatus::Dikembalikan)
            ->get(['status', 'grand_total']);

        return [
            'total_spent' => (int) $orders->sum('grand_total'),
            'order_count' => $orders->count(),
            'breakdown' => $this->breakdownByStatus($orders, 'grand_total'),
        ];
    }

    /**
     * @return array{total_income: int, order_count: int, incoming_count: int, processed_count: int, breakdown: list<array{status: string, count: int, total: int}>}
     */
    public function sellerIncome(Store $store): array
    {
        // Under escrow (Decision 3) the seller is only ever paid out on
        // Pesanan Selesai — income totals are based on realized payouts,
        // not every order the store has ever received.
        $orders = Order::query()
            ->where('store_id', $store->id)
            ->where('status', '!=', OrderStatus::Dikembalikan)
            ->get(['status', 'seller_income_amount']);

        $realized = $orders->where('status', OrderStatus::PesananSelesai);

        return [
            'total_income' => (int) $realized->sum('seller_income_amount'),
            'order_count' => $orders->count(),
            'incoming_count' => $orders->where('status', OrderStatus::SedangDikemas)->count(),
            'processed_count' => $orders->where('status', '!=', OrderStatus::SedangDikemas)->count(),
            'breakdown' => $this->breakdownByStatus($realized, 'seller_income_amount'),
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return list<array{status: string, count: int, total: int}>
     */
    private function breakdownByStatus(Collection $orders, string $amountColumn): array
    {
        return $orders->groupBy(fn (Order $order) => $order->status->value)
            ->map(fn (Collection $group, string $status) => [
                'status' => $status,
                'count' => $group->count(),
                'total' => (int) $group->sum($amountColumn),
            ])
            ->values()
            ->all();
    }
}
