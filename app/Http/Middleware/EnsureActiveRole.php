<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use App\Services\RoleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRole
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * Reject (403) unless the current request's active role — resolved
     * server-side from session (web) or token ability (api) — matches the
     * route's required role. Never trusts role from the request body (§4.3).
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        abort_unless(
            $this->roleService->resolveActiveRole($request) === RoleName::from($role),
            403,
        );

        return $next($request);
    }
}
