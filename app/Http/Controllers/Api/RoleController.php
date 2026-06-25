<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * Mint a role-scoped token and revoke the pending one used to call this
     * endpoint (§5.0) — only one live token per active-role session.
     */
    #[OA\Post(
        path: '/api/v1/role/select',
        tags: ['Auth'],
        summary: 'Pick the active role and mint a role-scoped token',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['role'],
                properties: [new OA\Property(property: 'role', type: 'string', enum: ['buyer', 'seller', 'driver'])],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Role-scoped token issued'),
            new OA\Response(response: 422, description: 'User does not own this role'),
        ],
    )]
    public function store(SelectRoleRequest $request): JsonResponse
    {
        $previous = $request->user()->currentAccessToken();
        $token = $this->roleService->issueApiToken($request->user(), $request->role());
        $previous->delete();

        return response()->json([
            'token' => $token->plainTextToken,
            'role' => $request->role()->value,
        ]);
    }
}
