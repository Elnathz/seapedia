<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MeController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    #[OA\Get(
        path: '/api/v1/me',
        tags: ['Auth'],
        summary: 'Get the authenticated user profile and active role',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'User profile with owned roles, active role, and wallet balance'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
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
