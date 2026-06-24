<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMonitorService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    public function __construct(private readonly AdminMonitorService $monitor) {}

    #[OA\Get(
        path: '/api/v1/admin/dashboard',
        tags: ['Admin'],
        summary: 'Live resource counts for the admin monitoring dashboard',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Resource counts snapshot'),
            new OA\Response(response: 403, description: 'Not an admin'),
        ],
    )]
    public function index(): JsonResponse
    {
        return response()->json($this->monitor->snapshot());
    }
}
