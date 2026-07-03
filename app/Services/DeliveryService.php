<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\WalletTransactionType;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class DeliveryService
{
    /**
     * How many jobs a driver may hold in flight at once (D3). The SPEC only
     * requires one active driver per order (5B), not one job per driver, so
     * batching a few pickups is allowed; three keeps routes manageable.
     */
    public const int MAX_ACTIVE_JOBS = 3;

    public function __construct(
        private readonly OrderService $orders,
        private readonly WalletService $wallets,
        private readonly ClockService $clock,
    ) {}

    /**
     * Jobs no driver has claimed yet, nearest first, eager-loaded (no N+1).
     * "Nearest" is the store→buyer distance frozen on the order at checkout
     * (D1), so drivers see the shortest trips at the top; ties fall back to
     * newest.
     */
    public function availableJobs(int $perPage = 10, ?string $method = null): LengthAwarePaginator
    {
        return Delivery::query()
            ->where('status', DeliveryStatus::Available)
            ->when($method, fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('delivery_method', $method)))
            ->with(['order.store:id,name'])
            ->orderBy(
                Order::query()
                    ->select('delivery_distance_km')
                    ->whereColumn('orders.id', 'deliveries.order_id')
            )
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * The driver's in-flight jobs (up to MAX_ACTIVE_JOBS, D3), oldest first so
     * the one taken earliest surfaces at the top to finish next.
     *
     * @return Collection<int, Delivery>
     */
    public function activeJobsFor(User $driver): Collection
    {
        return Delivery::query()
            ->where('driver_id', $driver->id)
            ->where('status', DeliveryStatus::Taken)
            ->with(['order.store:id,name', 'order.items'])
            ->oldest('taken_at')
            ->get();
    }

    /**
     * How many jobs the driver currently holds in flight (D3 cap check).
     */
    public function activeJobCountFor(User $driver): int
    {
        return Delivery::query()
            ->where('driver_id', $driver->id)
            ->where('status', DeliveryStatus::Taken)
            ->count();
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
     * if the driver already holds MAX_ACTIVE_JOBS in flight (D3); rejects
     * (409) if another driver already claimed this one.
     */
    public function take(Delivery $delivery, User $driver): Delivery
    {
        return DB::transaction(function () use ($delivery, $driver) {
            // Serialize this driver's takes so two concurrent claims can't
            // both slip past the cap: lock the driver row, then count the
            // in-flight jobs under that lock (D3).
            User::query()->lockForUpdate()->find($driver->id);

            if ($this->activeJobCountFor($driver) >= self::MAX_ACTIVE_JOBS) {
                throw ValidationException::withMessages([
                    'delivery' => [__('You already hold the maximum of :max active delivery jobs.', ['max' => self::MAX_ACTIVE_JOBS])],
                ]);
            }

            $locked = Delivery::query()->lockForUpdate()->findOrFail($delivery->id);

            if ($locked->driver_id !== null) {
                throw new ConflictHttpException(__('This job has already been taken.'));
            }

            $order = Order::query()->lockForUpdate()->findOrFail($locked->order_id);
            $order->load('store');

            // §MULTI-ROLE CONFLICT GUARD 2 — defense-in-depth (service layer)
            // DeliveryPolicy::take() is the primary gate, but a driver reaching
            // this method directly (e.g. via a future API refactor) must also be
            // blocked. We re-check inside the locked transaction to be certain.
            if ($order->buyer_id === $driver->id) {
                throw ValidationException::withMessages([
                    'delivery' => [__('Anda tidak dapat mengambil pekerjaan untuk pesanan yang Anda buat sendiri.')],
                ]);
            }

            // §MULTI-ROLE CONFLICT GUARD 3 — courier == seller owner
            if ($order->store->user_id === $driver->id) {
                throw ValidationException::withMessages([
                    'delivery' => [__('Anda tidak dapat mengambil pekerjaan untuk pesanan dari toko Anda sendiri.')],
                ]);
            }

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
