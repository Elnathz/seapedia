<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteJobRequest;
use App\Http\Requests\TakeJobRequest;
use App\Models\Delivery;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class JobController extends Controller
{
    public function __construct(private readonly DeliveryService $deliveries) {}

    #[OA\Get(
        path: '/api/v1/driver/jobs',
        tags: ['Driver Jobs'],
        summary: 'List available delivery jobs',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Paginated available jobs')],
    )]
    public function index(): JsonResponse
    {
        return response()->json($this->deliveries->availableJobs());
    }

    #[OA\Get(
        path: '/api/v1/driver/jobs/{delivery}',
        tags: ['Driver Jobs'],
        summary: 'Get one delivery job with its order',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Job detail')],
    )]
    public function show(Delivery $delivery): JsonResponse
    {
        return response()->json($delivery->load([
            'order.store:id,name',
            'order.items',
            'order.statusHistories' => fn ($query) => $query->oldest(),
            'driver:id,name',
        ]));
    }

    #[OA\Post(
        path: '/api/v1/driver/jobs/{delivery}/take',
        tags: ['Driver Jobs'],
        summary: 'Claim an available job (§6 double-take guard)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Job taken; order advanced to Sedang Dikirim'),
            new OA\Response(response: 409, description: 'Job already taken by another driver'),
            new OA\Response(response: 422, description: 'Driver already has an active job'),
        ],
    )]
    public function take(TakeJobRequest $request, Delivery $delivery): JsonResponse
    {
        $this->authorize('take', $delivery);

        $updated = $this->deliveries->take($delivery, $request->user());

        return response()->json($updated->load('order'));
    }

    #[OA\Post(
        path: '/api/v1/driver/jobs/{delivery}/complete',
        tags: ['Driver Jobs'],
        summary: 'Confirm delivery — escrow payout to driver and seller',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Delivery completed; order advanced to Pesanan Selesai'),
            new OA\Response(response: 403, description: 'Not the owning driver'),
            new OA\Response(response: 422, description: 'Not awaiting completion'),
        ],
    )]
    public function complete(CompleteJobRequest $request, Delivery $delivery): JsonResponse
    {
        $this->authorize('complete', $delivery);

        $updated = $this->deliveries->complete($delivery, $request->user());

        return response()->json($updated->load('order'));
    }
}
