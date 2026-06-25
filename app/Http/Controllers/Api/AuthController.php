<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiLoginRequest;
use App\Services\AuthService;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly RoleService $roleService,
    ) {}

    /**
     * Mint a Sanctum token for non-SPA `/api/v1` consumers (§5.0). A
     * single-role user (or admin) gets a role-scoped token immediately; a
     * multi-role user gets an unscoped token and must call `/role/select`.
     */
    #[OA\Post(
        path: '/api/v1/login',
        tags: ['Auth'],
        summary: 'Authenticate and mint a Sanctum token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Token issued (role-scoped or pending role selection)'),
            new OA\Response(response: 422, description: 'Invalid credentials'),
        ],
    )]
    public function store(ApiLoginRequest $request): JsonResponse
    {
        $result = $this->auth->loginViaApi($request->validated('email'), $request->validated('password'));

        abort_unless($result !== null, 422, 'Email atau kata sandi salah.');

        ['user' => $user, 'token' => $token] = $result;

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'is_admin' => $user->is_admin,
                'owned_roles' => array_map(fn ($role) => $role->value, $user->ownedRoles()),
            ],
            'active_role' => $this->roleService->roleFromAbilities($token->accessToken->abilities)?->value,
        ]);
    }

    /**
     * Revoke the token used for this request (§5.0 "logout invalidates
     * token" — proven by the same token being rejected on the next call).
     */
    #[OA\Post(
        path: '/api/v1/logout',
        tags: ['Auth'],
        summary: 'Revoke the current Sanctum token',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Token revoked')],
    )]
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
