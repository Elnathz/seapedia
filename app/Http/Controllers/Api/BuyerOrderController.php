<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BuyerOrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    #[OA\Get(
        path: '/api/v1/buyer/orders',
        tags: ['Buyer Orders'],
        summary: "List the buyer's orders",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Paginated orders, newest first')],
    )]
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->orders->forBuyer($request->user()));
    }

    #[OA\Get(
        path: '/api/v1/buyer/orders/{order}',
        tags: ['Buyer Orders'],
        summary: 'Get one order with its items and status history',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Order detail'),
            new OA\Response(response: 403, description: 'Not the owner'),
            new OA\Response(response: 404, description: 'Order not found'),
        ],
    )]
    public function show(int $order): JsonResponse
    {
        $fullOrder = $this->orders->findForBuyer($order);

        abort_if($fullOrder === null, 404);

        $this->authorize('view', $fullOrder);

        return response()->json($fullOrder);
    }
}
