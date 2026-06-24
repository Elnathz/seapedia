<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvanceClockRequest;
use App\Services\ClockService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ClockController extends Controller
{
    public function __construct(private readonly ClockService $clock) {}

    #[OA\Post(
        path: '/api/v1/admin/clock/advance',
        tags: ['Admin'],
        summary: 'Advance the simulated day by one tick (§5.7)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'simulated_now advanced by one day'),
            new OA\Response(response: 403, description: 'Not an admin'),
        ],
    )]
    public function advance(AdvanceClockRequest $request): JsonResponse
    {
        $next = $this->clock->advance(1);

        return response()->json(['simulated_now' => $next->toDateTimeString()]);
    }
}
