<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

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
}
