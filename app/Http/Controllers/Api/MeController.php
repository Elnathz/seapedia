<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeRole = $this->roleService->activeRoleFromToken($user);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'is_admin' => $user->is_admin,
            'owned_roles' => array_map(fn ($role) => $role->value, $user->ownedRoles()),
            'active_role' => $activeRole?->value,
            'wallet_balance' => $user->wallet?->balance,
        ]);
    }
}
