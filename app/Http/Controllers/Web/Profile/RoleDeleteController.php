<?php

namespace App\Http\Controllers\Web\Profile;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleDeleteController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * Resign a non-admin role. All guards (last-role, in-flight obligations,
     * seller store soft-hide) live in RoleService and throw back to the form.
     */
    public function destroy(Request $request, string $role): RedirectResponse
    {
        $roleName = RoleName::tryFrom($role);

        if (! $roleName) {
            return back()->withErrors(['role' => __('Role tidak valid.')]);
        }

        $this->roleService->removeRole($request->user(), $roleName);

        if ($this->roleService->activeRoleFromSession($request) === $roleName) {
            $request->session()->forget('active_role');
        }

        return redirect()->route('role.select');
    }
}
