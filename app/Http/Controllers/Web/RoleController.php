<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddRoleRequest;
use App\Http\Requests\SelectRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * Show the role-selection screen (§4.2 step 5). Reachable any time the
     * user wants to switch their active role, not just right after login.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('role/Select', [
            'roles' => array_map(
                fn ($role) => $role->value,
                $request->user()->ownedRoles(),
            ),
        ]);
    }

    public function store(SelectRoleRequest $request): RedirectResponse
    {
        $role = $request->role();
        $this->roleService->setActiveRoleInSession($request, $role);

        if ($role->value === RoleName::Buyer->value) {
            return redirect()->route('catalog.index');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Attach a new non-admin role the user does not yet own (guards live in
     * RoleService). Lands back on the role picker showing the updated set.
     */
    public function add(AddRoleRequest $request): RedirectResponse
    {
        $this->roleService->addRole($request->user(), $request->role());

        return redirect()->route('role.select');
    }
}
