<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
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
