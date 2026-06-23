<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
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

        if (! $token) {
            return null;
        }

        $ability = collect($token->abilities)
            ->first(fn (string $ability) => str_starts_with($ability, 'role:'));

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
        $user->roles()->attach(
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
            }
        }

        return route('dashboard', absolute: false);
    }
}
