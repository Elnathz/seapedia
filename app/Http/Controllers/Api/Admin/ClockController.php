<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvanceClockRequest;
use App\Services\ClockService;
use App\Services\OverdueService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ClockController extends Controller
{
    public function __construct(
        private readonly ClockService $clock,
        private readonly OverdueService $overdue,
    ) {}

    #[OA\Post(
        path: '/api/v1/admin/clock/advance',
        tags: ['Admin'],
        summary: 'Advance the simulated day by one tick and run the overdue sweep (§5.7, §5.9)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'simulated_now advanced by one day; refunded_count reports the overdue sweep'),
            new OA\Response(response: 403, description: 'Not an admin'),
        ],
    )]
    public function advance(AdvanceClockRequest $request): JsonResponse
    {
        $next = $this->clock->advance(1);
        $swept = $this->overdue->sweep();

        return response()->json([
            'simulated_now' => $next->toDateTimeString(),
            'refunded_count' => $swept['refunded_count'],
        ]);
    }
}
