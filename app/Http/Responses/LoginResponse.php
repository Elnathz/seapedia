<?php

namespace App\Http\Responses;

use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * §4.2 login flow: admin bypasses role selection; exactly one owned role
     * auto-selects and proceeds; multiple owned roles must not reach any
     * dashboard until the user picks one via the role-selection screen.
     */
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->is_admin) {
            $ownedRoles = $user->ownedRoles();

            if (count($ownedRoles) > 1) {
                return redirect()->route('role.select');
            }

            if (count($ownedRoles) === 1) {
                $this->roleService->setActiveRoleInSession($request, $ownedRoles[0]);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
