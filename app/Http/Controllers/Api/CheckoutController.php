<?php

namespace App\Http\Controllers\Api;

use App\Enums\DeliveryMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\PreviewCheckoutRequest;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Address;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    #[OA\Post(
        path: '/api/v1/buyer/checkout/preview',
        tags: ['Checkout'],
        summary: 'Preview the §5.2 money breakdown for a delivery method',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'subtotal/discount/tax/delivery_fee/grand_total + balance check'),
        ],
    )]
    public function preview(PreviewCheckoutRequest $request): JsonResponse
    {
        $deliveryMethod = DeliveryMethod::from($request->validated()['delivery_method']);

        return response()->json($this->checkout->preview($request->user(), $deliveryMethod));
    }

    #[OA\Post(
        path: '/api/v1/buyer/checkout',
        tags: ['Checkout'],
        summary: 'Commit checkout: charge wallet, reduce stock, create the order',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Order created in Sedang Dikemas'),
            new OA\Response(response: 422, description: 'Insufficient stock or insufficient wallet balance'),
        ],
    )]
    public function store(StoreCheckoutRequest $request): JsonResponse
    {
        $data = $request->validated();
        $address = Address::findOrFail($data['address_id']);
        $this->authorize('update', $address);

        $order = $this->checkout->commit(
            $request->user(),
            $address,
            DeliveryMethod::from($data['delivery_method']),
        );

        return response()->json($order->load('items'), 201);
    }
}
