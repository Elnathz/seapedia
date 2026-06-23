<?php

namespace App\Http\Responses;

use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * A fresh registration is treated as a first login (§4.2): the roles
     * chosen at signup decide whether the user lands on a dashboard or the
     * role-selection screen.
     */
    public function toResponse($request): RedirectResponse
    {
        return redirect()->intended($this->roleService->resolvePostAuthRedirect($request));
    }
}
