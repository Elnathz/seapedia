<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AccountService
{
    /**
     * @var list<OrderStatus>
     */
    private const ACTIVE_ORDER_STATUSES = [
        OrderStatus::SedangDikemas,
        OrderStatus::MenungguPengirim,
        OrderStatus::SedangDikirim,
    ];

    /**
     * Soft-delete + anonymize an account. Refuses while the user still has any
     * in-flight obligation (buyer/seller orders, driver delivery) or a non-zero
     * wallet balance — there is no withdrawal path, so money must not silently
     * vanish. Soft delete keeps dependent orders/deliveries/reviews intact for
     * the actors that reference them; anonymizing frees the unique
     * email/username so the person can register fresh later.
     */
    public function delete(User $user): void
    {
        $this->guardNoInFlightObligations($user);
        $this->guardWalletEmpty($user);

        DB::transaction(function () use ($user) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->tokens()->delete();

            $user->forceFill([
                'name' => 'Pengguna Terhapus',
                'username' => 'deleted_'.$user->id,
                'email' => 'deleted+'.$user->id.'@seapedia.invalid',
                'phone' => null,
                'avatar_path' => null,
            ])->save();

            $user->delete(); // soft delete (SoftDeletes) — no FK cascade fires
        });
    }

    private function guardNoInFlightObligations(User $user): void
    {
        $hasBuyerOrders = Order::query()
            ->where('buyer_id', $user->id)
            ->whereIn('status', self::ACTIVE_ORDER_STATUSES)
            ->exists();

        $hasSellerOrders = $user->store && Order::query()
            ->where('store_id', $user->store->id)
            ->whereIn('status', self::ACTIVE_ORDER_STATUSES)
            ->exists();

        $hasDelivery = Delivery::query()
            ->where('driver_id', $user->id)
            ->where('status', DeliveryStatus::Taken)
            ->exists();

        if ($hasBuyerOrders || $hasSellerOrders || $hasDelivery) {
            throw ValidationException::withMessages([
                'password' => __('Tidak dapat menghapus akun karena masih ada pesanan atau pengiriman aktif.'),
            ]);
        }
    }

    private function guardWalletEmpty(User $user): void
    {
        $balance = $user->wallet?->balance ?? 0;

        if ($balance > 0) {
            throw ValidationException::withMessages([
                'password' => __('Habiskan saldo wallet (Rp:balance) sebelum menghapus akun.', [
                    'balance' => number_format($balance, 0, ',', '.'),
                ]),
            ]);
        }
    }
}
