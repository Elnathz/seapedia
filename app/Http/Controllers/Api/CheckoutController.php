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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['delivery_method'],
                properties: [
                    new OA\Property(property: 'delivery_method', type: 'string', enum: ['pickup', 'delivery']),
                    new OA\Property(property: 'promo_code', type: 'string', nullable: true, maxLength: 32),
                    new OA\Property(property: 'voucher_code', type: 'string', nullable: true, maxLength: 32),
                    new OA\Property(property: 'address_id', type: 'integer', nullable: true, description: 'Ships-to address; its coordinates set the distance delivery fee'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'subtotal/discount/tax/delivery_fee (base+distance+weight)/grand_total + balance check'),
        ],
    )]
    public function preview(PreviewCheckoutRequest $request): JsonResponse
    {
        $data = $request->validated();
        $deliveryMethod = DeliveryMethod::from($data['delivery_method']);

        // Scoped to the caller's own addresses, so a foreign id resolves to
        // null (base fee only) with no leak.
        $address = isset($data['address_id'])
            ? $request->user()->addresses()->find($data['address_id'])
            : null;

        return response()->json($this->checkout->preview(
            $request->user(),
            $deliveryMethod,
            $data['promo_code'] ?? null,
            $data['voucher_code'] ?? null,
            $address,
        ));
    }

    #[OA\Post(
        path: '/api/v1/buyer/checkout',
        tags: ['Checkout'],
        summary: 'Commit checkout: charge wallet, reduce stock, create the order',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['address_id', 'delivery_method'],
                properties: [
                    new OA\Property(property: 'address_id', type: 'integer'),
                    new OA\Property(property: 'delivery_method', type: 'string', enum: ['pickup', 'delivery']),
                    new OA\Property(property: 'promo_code', type: 'string', nullable: true, maxLength: 32),
                    new OA\Property(property: 'voucher_code', type: 'string', nullable: true, maxLength: 32),
                ],
            ),
        ),
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
            $data['promo_code'] ?? null,
            $data['voucher_code'] ?? null,
        );

        return response()->json($order->load('items'), 201);
    }
}
