<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Locked transition table (§5.6) — the only valid (from → to) moves.
     * "(create)" is handled by createFromCheckout(), not here.
     *
     * @var array<string, array<string>>
     */
    private const array TRANSITIONS = [
        'sedang_dikemas' => ['menunggu_pengirim', 'dikembalikan'],
        'menunggu_pengirim' => ['sedang_dikirim', 'dikembalikan'],
        'sedang_dikirim' => ['pesanan_selesai', 'dikembalikan'],
    ];

    public function __construct(private readonly ClockService $clock) {}

    /**
     * Create an order in Sedang Dikemas with its first status-history row.
     * Called only from inside CheckoutService::commit()'s locked
     * transaction — stock/wallet effects happen there, not here.
     *
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $items
     */
    public function createFromCheckout(array $data, array $items, User $buyer): Order
    {
        $order = Order::create([
            ...$data,
            'code' => $this->uniqueCode(),
            'buyer_id' => $buyer->id,
            'status' => OrderStatus::SedangDikemas,
        ]);

        foreach ($items as $item) {
            $order->items()->create($item);
        }

        $this->writeHistory($order, OrderStatus::SedangDikemas, 'Checkout berhasil', $buyer->id);

        return $order;
    }

    /**
     * The only way an order's status may change (golden rule 7). Throws
     * (422) on any transition not in the locked table — no silent changes.
     */
    public function transition(Order $order, OrderStatus $to, ?int $changedBy = null, ?string $note = null): Order
    {
        $allowed = self::TRANSITIONS[$order->status->value] ?? [];

        if (! in_array($to->value, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => [__('Invalid order status transition.')],
            ]);
        }

        $order->update(['status' => $to]);
        $this->writeHistory($order, $to, $note, $changedBy);

        return $order->refresh();
    }

    /**
     * The only seller-initiated transition (§5.6): Sedang Dikemas →
     * Menunggu Pengirim, plus — in the same transaction — the auto-created
     * `available` delivery job a driver will see (§13 Day 5). Idempotent:
     * `firstOrCreate` never inserts a second delivery row for the order.
     */
    public function processBySeller(Order $order, int $changedBy): Order
    {
        return DB::transaction(function () use ($order, $changedBy) {
            $this->transition($order, OrderStatus::MenungguPengirim, $changedBy, 'Diproses oleh penjual');

            Delivery::query()->firstOrCreate(
                ['order_id' => $order->id],
                ['status' => DeliveryStatus::Available],
            );

            return $order;
        });
    }

    /**
     * The buyer's own orders, newest first, eager-loaded for the history
     * list (no N+1).
     */
    public function forBuyer(User $buyer, int $perPage = 10, ?OrderStatus $status = null): LengthAwarePaginator
    {
        return Order::query()
            ->where('buyer_id', $buyer->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with([
                'store:id,name,slug',
                'items' => fn ($q) => $q->select('id', 'order_id', 'product_id', 'product_name_snapshot')->with('product.images:id,product_id,image_path'),
            ])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Full detail for one order — items, status history, and the buyer's
     * own store/buyer relations, eager-loaded (no N+1).
     */
    public function findForBuyer(int $orderId): ?Order
    {
        return Order::query()
            ->with([
                'items.product.images',
                'items.variant',
                'statusHistories' => fn ($query) => $query->oldest(),
                'store:id,name,slug',
                'delivery.driver:id,name',
            ])
            ->find($orderId);
    }

    /**
     * A seller's incoming orders for their store, newest first.
     */
    public function forSeller(Store $store, int $perPage = 10, ?OrderStatus $status = null): LengthAwarePaginator
    {
        return Order::query()
            ->where('store_id', $store->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with('buyer:id,name')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Full detail for one order from the seller's side — items, status
     * history, and the owning store (with `user_id` for the policy check),
     * eager-loaded (no N+1).
     */
    public function findForSeller(int $orderId): ?Order
    {
        return Order::query()
            ->with([
                'items',
                'statusHistories' => fn ($query) => $query->oldest(),
                'store:id,name,slug,user_id',
                'buyer:id,name',
                'delivery.driver:id,name',
            ])
            ->find($orderId);
    }

    private function writeHistory(Order $order, OrderStatus $status, ?string $note, ?int $changedBy): void
    {
        $order->statusHistories()->create([
            'status' => $status->value,
            'note' => $note,
            'changed_by' => $changedBy,
            'created_at' => $this->clock->now(),
        ]);
    }

    private function uniqueCode(): string
    {
        do {
            $code = 'INV-'.$this->clock->now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('code', $code)->exists());

        return $code;
    }
}
