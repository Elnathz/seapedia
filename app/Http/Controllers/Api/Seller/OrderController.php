<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    #[OA\Post(
        path: '/api/v1/seller/orders/{order}/process',
        tags: ['Seller Orders'],
        summary: 'Process an incoming order: Sedang Dikemas → Menunggu Pengirim',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Order advanced to Menunggu Pengirim'),
            new OA\Response(response: 403, description: 'Not the owning seller'),
            new OA\Response(response: 422, description: 'Invalid status transition'),
        ],
    )]
    public function process(Request $request, Order $order): JsonResponse
    {
        $this->authorize('process', $order);

        $updated = $this->orders->processBySeller($order, $request->user()->id);

        return response()->json($updated->load('statusHistories'));
    }
}
