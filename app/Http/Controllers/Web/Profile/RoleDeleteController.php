<?php

namespace App\Http\Controllers\Web\Profile;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleDeleteController extends Controller
{
    public function destroy(Request $request, string $roleName): RedirectResponse
    {
        $user = $request->user();

        if ($roleName === 'driver') {
            $hasActiveDelivery = Delivery::where('driver_id', $user->id)
                ->whereIn('status', [DeliveryStatus::Available, DeliveryStatus::Taken])
                ->exists();

            if ($hasActiveDelivery) {
                return back()->withErrors([
                    'role' => 'Selesaikan semua pengiriman aktif sebelum melepas role Driver.',
                ]);
            }
        }

        if ($roleName === 'seller') {
            $hasActiveOrders = Order::where('store_id', $user->store?->id)
                ->whereIn('status', [
                    OrderStatus::SedangDikemas,
                    OrderStatus::MenungguPengirim,
                    OrderStatus::SedangDikirim,
                ])
                ->exists();

            if ($hasActiveOrders) {
                return back()->withErrors([
                    'role' => 'Tidak dapat melepas role seller saat masih ada pesanan aktif.',
                ]);
            }

            // Delete store as well
            if ($user->store) {
                $user->store->delete();
            }
        }

        if ($roleName === 'buyer') {
            $hasActiveOrders = Order::where('buyer_id', $user->id)
                ->whereIn('status', [
                    OrderStatus::BelumDibayar,
                    OrderStatus::SedangDikemas,
                    OrderStatus::MenungguPengirim,
                    OrderStatus::SedangDikirim,
                ])
                ->exists();

            if ($hasActiveOrders) {
                return back()->withErrors([
                    'role' => 'Tidak dapat melepas role buyer saat masih ada pesanan aktif.',
                ]);
            }
        }

        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $user->roles()->detach($role->id);

            // If they just removed their active role, clear it from session
            if (session('active_role') === $roleName) {
                session()->forget('active_role');
            }
        }

        return redirect()->route('role.select');
    }
}
