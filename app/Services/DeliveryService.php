<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\WalletTransactionType;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class DeliveryService
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly WalletService $wallets,
        private readonly ClockService $clock,
    ) {}

    /**
     * Jobs no driver has claimed yet, newest first, eager-loaded (no N+1).
     */
    public function availableJobs(int $perPage = 10): LengthAwarePaginator
    {
        return Delivery::query()
            ->where('status', DeliveryStatus::Available)
            ->with(['order.store:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * The driver's single in-flight job (Decision 6: one active job at a
     * time), or null if they have none.
     */
    public function activeJobFor(User $driver): ?Delivery
    {
        return Delivery::query()
            ->where('driver_id', $driver->id)
            ->where('status', DeliveryStatus::Taken)
            ->with(['order.store:id,name', 'order.items'])
            ->first();
    }

    /**
     * The driver's completed jobs, newest first, plus their summed
     * earnings — feeds the dashboard's history + stat cards.
     */
    public function historyFor(User $driver, int $perPage = 10): LengthAwarePaginator
    {
        return Delivery::query()
            ->where('driver_id', $driver->id)
            ->where('status', DeliveryStatus::Completed)
            ->with(['order:id,code,store_id', 'order.store:id,name'])
            ->latest('completed_at')
            ->paginate($perPage);
    }

    public function totalEarningsFor(User $driver): int
    {
        return (int) Delivery::query()
            ->where('driver_id', $driver->id)
            ->where('status', DeliveryStatus::Completed)
            ->sum('earning_amount');
    }

    /**
     * Claim an unclaimed job (§6 double-take guard via row lock) and
     * advance the order Menunggu Pengirim → Sedang Dikirim. Rejects (422)
     * if the driver already holds an active job (Decision 6); rejects
     * (409) if another driver already claimed this one.
     */
    public function take(Delivery $delivery, User $driver): Delivery
    {
        return DB::transaction(function () use ($delivery, $driver) {
            if ($this->activeJobFor($driver) !== null) {
                throw ValidationException::withMessages([
                    'delivery' => [__('You already have an active delivery job.')],
                ]);
            }

            $locked = Delivery::query()->lockForUpdate()->findOrFail($delivery->id);

            if ($locked->driver_id !== null) {
                throw new ConflictHttpException(__('This job has already been taken.'));
            }

            $order = Order::query()->lockForUpdate()->findOrFail($locked->order_id);

            $locked->update([
                'driver_id' => $driver->id,
                'status' => DeliveryStatus::Taken,
                'taken_at' => $this->clock->now(),
            ]);

            $this->orders->transition($order, OrderStatus::SedangDikirim, $driver->id, 'Diambil oleh kurir');

            return $locked->refresh();
        });
    }

    /**
     * Confirm delivery and advance the order Sedang Dikirim → Pesanan
     * Selesai. In the same locked transaction: credit the driver 80% of
     * the delivery fee (earning) AND release the seller's escrowed income
     * (Decision 3) — the seller is paid for the first time here, not at
     * checkout. Rejects (403) for a non-owning driver; rejects (422) if
     * the job isn't awaiting completion (no double payout on a re-run).
     */
    public function complete(Delivery $delivery, User $driver): Delivery
    {
        return DB::transaction(function () use ($delivery, $driver) {
            $locked = Delivery::query()->lockForUpdate()->findOrFail($delivery->id);

            abort_unless($locked->driver_id === $driver->id, 403);

            if ($locked->status !== DeliveryStatus::Taken) {
                throw ValidationException::withMessages([
                    'delivery' => [__('This delivery is not awaiting completion.')],
                ]);
            }

            $order = Order::query()->lockForUpdate()->findOrFail($locked->order_id);
            $earning = intdiv($order->delivery_fee * 80, 100);

            $locked->update([
                'status' => DeliveryStatus::Completed,
                'completed_at' => $this->clock->now(),
                'earning_amount' => $earning,
            ]);

            $this->wallets->credit(
                $driver->wallet,
                $earning,
                WalletTransactionType::Earning,
                'delivery',
                $locked->id,
            );

            $this->wallets->credit(
                $order->store->user->wallet,
                $order->seller_income_amount,
                WalletTransactionType::Income,
                'order',
                $order->id,
            );

            $this->orders->transition($order, OrderStatus::PesananSelesai, $driver->id, 'Pesanan diterima pembeli');

            return $locked->refresh();
        });
    }
}
