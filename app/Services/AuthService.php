<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

class AuthService
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * Verify credentials and mint the initial Sanctum token for non-SPA
     * `/api/v1` consumers (§5.0). A user who owns exactly one role (or is
     * admin) gets a role-scoped token immediately; a multi-role user gets
     * an unscoped token and must call `/role/select` next.
     *
     * @return array{user: User, token: NewAccessToken}|null null on bad credentials.
     */
    public function loginViaApi(string $email, string $password): ?array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return ['user' => $user, 'token' => $this->issueInitialToken($user)];
    }

    private function issueInitialToken(User $user): NewAccessToken
    {
        if ($user->is_admin) {
            return $user->createToken('session', ['admin']);
        }

        $ownedRoles = $user->ownedRoles();

        return count($ownedRoles) === 1
            ? $this->roleService->issueApiToken($user, $ownedRoles[0])
            : $user->createToken('session-pending', []);
    }
}
