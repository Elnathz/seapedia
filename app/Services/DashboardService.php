<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Models\User;
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
                'props' => ['balance' => $balance, 'activeProducts' => $this->activeProductCount($user)],
            ],
            RoleName::Driver => [
                'component' => 'dashboard/Driver',
                'props' => [
                    'activeJob' => $this->deliveries->activeJobFor($user),
                    'history' => $this->deliveries->historyFor($user, perPage: 5),
                    'totalEarnings' => $this->deliveries->totalEarningsFor($user),
                ],
            ],
            RoleName::Buyer, null => [
                'component' => 'dashboard/Buyer',
                'props' => ['balance' => $balance, 'activeOrders' => $this->activeOrderCount($user)],
            ],
        };
    }

    private function activeProductCount(User $user): int
    {
        return $user->store?->products()->where('is_active', true)->count() ?? 0;
    }

    private function activeOrderCount(User $user): int
    {
        return $user->orders()->whereNotIn('status', self::FINAL_ORDER_STATUSES)->count();
    }
}
