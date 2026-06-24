<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promo;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Builder;

class AdminMonitorService
{
    public function __construct(
        private readonly ClockService $clock,
        private readonly OverdueService $overdue,
    ) {}

    /**
     * Live resource counts for the admin monitoring dashboard — every
     * figure is one aggregate query, never a per-row loop (golden rule 16).
     *
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return [
            'simulated_now' => $this->clock->now()->toDateTimeString(),
            'users' => $this->userCounts(),
            'stores_count' => Store::query()->count(),
            'products_count' => Product::query()->count(),
            'orders_by_status' => $this->countByColumn(Order::query(), 'status'),
            'deliveries_by_status' => $this->countByColumn(Delivery::query(), 'status'),
            'overdue_eligible_count' => $this->overdue->eligibleCount(),
            'promos' => $this->discountCounts(Promo::query()),
            'vouchers' => $this->voucherCounts(),
        ];
    }

    /**
     * @return array{total: int, admins: int, buyers: int, sellers: int, drivers: int}
     */
    private function userCounts(): array
    {
        $byRole = Role::query()
            ->withCount('users')
            ->get()
            ->pluck('users_count', 'name');

        return [
            'total' => User::query()->count(),
            'admins' => User::query()->where('is_admin', true)->count(),
            'buyers' => (int) ($byRole[RoleName::Buyer->value] ?? 0),
            'sellers' => (int) ($byRole[RoleName::Seller->value] ?? 0),
            'drivers' => (int) ($byRole[RoleName::Driver->value] ?? 0),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function countByColumn(Builder $query, string $column): array
    {
        return $query->select($column)
            ->selectRaw('count(*) as aggregate')
            ->groupBy($column)
            ->pluck('aggregate', $column)
            ->all();
    }

    /**
     * @return array{total: int, active: int, expired: int}
     */
    private function discountCounts(Builder $query): array
    {
        $now = $this->clock->now();

        return [
            'total' => $query->count(),
            'active' => (clone $query)->where('is_active', true)->where('expiry_date', '>', $now)->count(),
            'expired' => (clone $query)->where('expiry_date', '<=', $now)->count(),
        ];
    }

    /**
     * @return array{total: int, active: int, expired: int, used_up: int}
     */
    private function voucherCounts(): array
    {
        $base = $this->discountCounts(Voucher::query());

        return [
            ...$base,
            'used_up' => Voucher::query()->whereColumn('used_count', '>=', 'usage_limit')->count(),
        ];
    }
}
