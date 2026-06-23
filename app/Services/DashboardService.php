<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardService
{
    public function __construct(private readonly RoleService $roleService) {}

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
                'props' => ['stats' => $this->adminStats()],
            ];
        }

        $balance = $user->wallet?->balance ?? 0;
        $activeRole = $this->roleService->resolveActiveRole($request);

        return match ($activeRole) {
            RoleName::Seller => [
                'component' => 'dashboard/Seller',
                'props' => ['balance' => $balance, 'activeProducts' => 0],
            ],
            RoleName::Driver => [
                'component' => 'dashboard/Driver',
                'props' => ['activeDeliveries' => 0],
            ],
            RoleName::Buyer, null => [
                'component' => 'dashboard/Buyer',
                'props' => ['balance' => $balance, 'activeOrders' => 0],
            ],
        };
    }

    /**
     * @return array{totalUsers: int, totalSellers: int, totalBuyers: int, totalDrivers: int}
     */
    private function adminStats(): array
    {
        return [
            'totalUsers' => User::query()->count(),
            'totalSellers' => $this->countByRole(RoleName::Seller),
            'totalBuyers' => $this->countByRole(RoleName::Buyer),
            'totalDrivers' => $this->countByRole(RoleName::Driver),
        ];
    }

    private function countByRole(RoleName $role): int
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', $role->value))
            ->count();
    }
}
