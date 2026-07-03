<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DashboardService
{
    /**
     * Orders that have reached a final state — not counted as "active".
     */
    private const array FINAL_ORDER_STATUSES = [
        OrderStatus::PesananSelesai->value,
        OrderStatus::Dikembalikan->value,
    ];

    public function __construct(
        private readonly RoleService $roleService,
        private readonly DeliveryService $deliveries,
        private readonly AdminMonitorService $adminMonitor,
    ) {}

    /**
     * Decide which dashboard shell a request's user sees and the props it
     * needs. Admin bypasses the active role entirely (§4.2 admin path); a
     * user owning no role yet (not a case the TDD defines) falls back to
     * the buyer shell, matching the existing post-auth-redirect behavior.
     *
     * @return array{component: string, props: array<string, mixed>}
     */
    public function buildView(Request $request): array
    {
        $user = $request->user();

        if ($user->is_admin) {
            return [
                'component' => 'dashboard/Admin',
                'props' => ['snapshot' => $this->adminMonitor->snapshot()],
            ];
        }

        $balance = $user->wallet?->balance ?? 0;
        $activeRole = $this->roleService->resolveActiveRole($request);

        return match ($activeRole) {
            RoleName::Seller => [
                'component' => 'dashboard/Seller',
                'props' => [
                    'balance' => $balance,
                    'activeProducts' => $this->activeProductCount($user),
                    'onboarding' => $this->sellerOnboarding($user),
                    'stats' => $this->sellerStats($user),
                ],
            ],
            RoleName::Driver => [
                'component' => 'dashboard/Driver',
                'props' => [
                    'activeJobs' => $this->deliveries->activeJobsFor($user),
                    'maxActiveJobs' => $this->deliveries->maxActiveJobsFor($user),
                    'history' => $this->deliveries->historyFor($user, perPage: 5),
                    'totalEarnings' => $this->deliveries->totalEarningsFor($user),
                ],
            ],
            RoleName::Buyer, null => [
                'component' => 'dashboard/Buyer',
                'props' => [
                    'balance' => $balance,
                    'activeOrdersCount' => $this->activeOrderCount($user),
                    'completedOrdersCount' => $this->completedOrderCount($user),
                    'recentActiveOrders' => $this->recentActiveOrders($user, 3),
                ],
            ],
        };
    }

    private function activeProductCount(User $user): int
    {
        return $user->store?->products()->where('is_active', true)->count() ?? 0;
    }

    /**
     * The seller's order pipeline at a glance: how many orders sit at each
     * stage (so "Perlu Diproses" can nudge the seller to act) plus the income
     * already released from completed orders (§ escrow settles on completion).
     *
     * @return array{awaiting_process: int, awaiting_pickup: int, in_delivery: int, completed: int, revenue: int}
     */
    private function sellerStats(User $user): array
    {
        $storeId = $user->store?->id;

        if ($storeId === null) {
            return [
                'awaiting_process' => 0,
                'awaiting_pickup' => 0,
                'in_delivery' => 0,
                'completed' => 0,
                'revenue' => 0,
            ];
        }

        $countAt = fn (OrderStatus $status): int => Order::query()
            ->where('store_id', $storeId)
            ->where('status', $status->value)
            ->count();

        return [
            'awaiting_process' => $countAt(OrderStatus::SedangDikemas),
            'awaiting_pickup' => $countAt(OrderStatus::MenungguPengirim),
            'in_delivery' => $countAt(OrderStatus::SedangDikirim),
            'completed' => $countAt(OrderStatus::PesananSelesai),
            'revenue' => (int) Order::query()
                ->where('store_id', $storeId)
                ->where('status', OrderStatus::PesananSelesai->value)
                ->sum('seller_income_amount'),
        ];
    }

    /**
     * First-run setup checklist for a seller: each milestone that makes the
     * store ready to sell, so the dashboard can guide a new seller and fade
     * out once everything is in place.
     *
     * @return array{has_store: bool, has_address: bool, has_logo: bool, has_product: bool}
     */
    private function sellerOnboarding(User $user): array
    {
        $store = $user->store;

        return [
            'has_store' => $store !== null,
            'has_address' => $store !== null
                && filled($store->full_address)
                && $store->origin_latitude !== null
                && $store->origin_longitude !== null,
            'has_logo' => $store !== null && filled($store->logo_path),
            'has_product' => $store !== null && $store->products()->exists(),
        ];
    }

    private function activeOrderCount(User $user): int
    {
        return $user->orders()->whereNotIn('status', self::FINAL_ORDER_STATUSES)->count();
    }

    private function completedOrderCount(User $user): int
    {
        return $user->orders()->where('status', OrderStatus::PesananSelesai->value)->count();
    }

    /**
     * @return Collection<int, Order>
     */
    private function recentActiveOrders(User $user, int $limit)
    {
        return $user->orders()
            ->with(['store', 'items.product'])
            ->whereNotIn('status', self::FINAL_ORDER_STATUSES)
            ->latest()
            ->take($limit)
            ->get();
    }
}
