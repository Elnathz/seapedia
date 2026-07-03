<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class RoleService
{
    /**
     * Mint a Sanctum token scoped to a single active role. The token's
     * ability carries the role — the frontend never dictates it (§5.0, §4.3).
     */
    public function issueApiToken(User $user, RoleName $role): NewAccessToken
    {
        return $user->createToken('session', ["role:{$role->value}"]);
    }

    /**
     * Resolve the active role carried by the current request's API token
     * ability (e.g. "role:buyer"). Null if unauthenticated via token or the
     * token carries no role ability.
     */
    public function activeRoleFromToken(User $user): ?RoleName
    {
        $token = $user->currentAccessToken();

        return $token instanceof PersonalAccessToken ? $this->roleFromAbilities($token->abilities) : null;
    }

    /**
     * Pick out the "role:*" ability from a token's ability list, if any.
     * Shared by token resolution (above) and fresh-login token issuance.
     *
     * @param  array<int, string>  $abilities
     */
    public function roleFromAbilities(array $abilities): ?RoleName
    {
        $ability = collect($abilities)->first(fn (string $ability) => str_starts_with($ability, 'role:'));

        return $ability ? RoleName::from(substr($ability, 5)) : null;
    }

    /**
     * Persist the chosen active role for a web (session-guard) request (§4.1).
     */
    public function setActiveRoleInSession(Request $request, RoleName $role): void
    {
        $request->session()->put('active_role', $role->value);
    }

    /**
     * Read the active role stashed in the session by a web request.
     */
    public function activeRoleFromSession(Request $request): ?RoleName
    {
        $value = $request->session()->get('active_role');

        return $value ? RoleName::tryFrom($value) : null;
    }

    /**
     * Resolve the active role for the current request regardless of guard:
     * a Sanctum-token request reads the token ability, a session-guard
     * request reads the session. This is what `EnsureActiveRole` checks (§4.3).
     */
    public function resolveActiveRole(Request $request): ?RoleName
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        return $user->currentAccessToken() instanceof PersonalAccessToken
            ? $this->activeRoleFromToken($user)
            : $this->activeRoleFromSession($request);
    }

    /**
     * Attach the chosen role(s) to a newly registered user (§13 "registers,
     * picks role"). Caller validates $roleNames against RoleName first.
     *
     * @param  array<int, string>  $roleNames
     */
    public function assignRoles(User $user, array $roleNames): void
    {
        $user->roles()->syncWithoutDetaching(
            Role::query()->whereIn('name', $roleNames)->pluck('id'),
        );
    }

    /**
     * Decide where a freshly authenticated user should land and, if they
     * own exactly one role, commit it as active (§4.2). Shared by the
     * login and register Fortify responses so the rule lives in one place.
     */
    public function resolvePostAuthRedirect(Request $request): string
    {
        $user = $request->user();

        if (! $user->is_admin) {
            $ownedRoles = $user->ownedRoles();

            if (count($ownedRoles) > 1) {
                return route('role.select');
            }

            if (count($ownedRoles) === 1) {
                $this->setActiveRoleInSession($request, $ownedRoles[0]);

                if ($ownedRoles[0] === RoleName::Buyer) {
                    return route('catalog.index', absolute: false);
                }
            }
        }

        return route('dashboard', absolute: false);
    }

    /**
     * Order statuses that still bind a buyer/seller to an in-flight order and
     * therefore block resigning the related role or deleting the account.
     *
     * @var list<OrderStatus>
     */
    private const ACTIVE_ORDER_STATUSES = [
        OrderStatus::SedangDikemas,
        OrderStatus::MenungguPengirim,
        OrderStatus::SedangDikirim,
    ];

    /**
     * Detach a non-admin role after enforcing that (a) it is not the user's
     * last role and (b) it has no in-flight obligations. Seller removal
     * SOFT-HIDES the store — never hard-deletes it, because orders.store_id is
     * cascadeOnDelete and would take completed orders/histories/deliveries with
     * it. Throws ValidationException (keyed "role") on any guard failure.
     */
    public function removeRole(User $user, RoleName $role): void
    {
        if (count($user->ownedRoles()) <= 1) {
            throw ValidationException::withMessages([
                'role' => __('Tidak dapat melepas role terakhir. Hapus akun jika ingin keluar sepenuhnya.'),
            ]);
        }

        match ($role) {
            RoleName::Driver => $this->guardNoActiveDelivery($user),
            RoleName::Buyer => $this->guardNoActiveBuyerOrders($user),
            RoleName::Seller => $this->guardAndCloseSellerStore($user),
            default => throw ValidationException::withMessages([
                'role' => __('Role ini tidak dapat dilepas.'),
            ]),
        };

        DB::transaction(function () use ($user, $role) {
            $roleModel = Role::query()->where('name', $role->value)->first();
            if ($roleModel) {
                $user->roles()->detach($roleModel->id);
            }
        });
    }

    /**
     * Attach a non-admin role the user does not yet own. Admin is never
     * self-assignable (§ admin setup is seed/documented only). Re-adding the
     * Seller role restores a previously soft-hidden store.
     */
    public function addRole(User $user, RoleName $role): void
    {
        if (! in_array($role, [RoleName::Buyer, RoleName::Seller, RoleName::Driver], true)) {
            throw ValidationException::withMessages([
                'role' => __('Role ini tidak dapat ditambahkan.'),
            ]);
        }

        if ($user->hasRole($role)) {
            throw ValidationException::withMessages([
                'role' => __('Kamu sudah memiliki role ini.'),
            ]);
        }

        DB::transaction(function () use ($user, $role) {
            $this->assignRoles($user, [$role->value]);

            if ($role === RoleName::Seller) {
                Store::onlyTrashed()->where('user_id', $user->id)->first()?->restore();
            }
        });
    }

    private function guardNoActiveDelivery(User $user): void
    {
        $hasActive = Delivery::query()
            ->where('driver_id', $user->id)
            ->where('status', DeliveryStatus::Taken)
            ->exists();

        if ($hasActive) {
            throw ValidationException::withMessages([
                'role' => __('Selesaikan semua pengiriman aktif sebelum melepas role Driver.'),
            ]);
        }
    }

    private function guardNoActiveBuyerOrders(User $user): void
    {
        $hasActive = Order::query()
            ->where('buyer_id', $user->id)
            ->whereIn('status', self::ACTIVE_ORDER_STATUSES)
            ->exists();

        if ($hasActive) {
            throw ValidationException::withMessages([
                'role' => __('Tidak dapat melepas role Buyer saat masih ada pesanan aktif.'),
            ]);
        }
    }

    private function guardAndCloseSellerStore(User $user): void
    {
        $store = $user->store;

        if (! $store) {
            return;
        }

        $hasActive = Order::query()
            ->where('store_id', $store->id)
            ->whereIn('status', self::ACTIVE_ORDER_STATUSES)
            ->exists();

        if ($hasActive) {
            throw ValidationException::withMessages([
                'role' => __('Tidak dapat melepas role Seller saat masih ada pesanan aktif.'),
            ]);
        }

        $store->delete(); // soft-delete: catalog hides it, orders stay intact
    }
}
