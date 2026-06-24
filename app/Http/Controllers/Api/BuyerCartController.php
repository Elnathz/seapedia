<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BuyerCartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    #[OA\Get(
        path: '/api/v1/buyer/cart',
        tags: ['Buyer Cart'],
        summary: "Get the buyer's cart summary",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Cart with its store and items')],
    )]
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->carts->summary($request->user()));
    }

    #[OA\Post(
        path: '/api/v1/buyer/cart/items',
        tags: ['Buyer Cart'],
        summary: 'Add a product to the cart (single-store guard, §5.8)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Item added'),
            new OA\Response(response: 422, description: 'Cart holds items from a different store'),
        ],
    )]
    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        $item = $this->carts->addItem(
            $request->user(),
            $product,
            $data['quantity'],
            (bool) ($data['replace'] ?? false),
        );

        return response()->json($item);
    }

    #[OA\Put(
        path: '/api/v1/buyer/cart/items/{item}',
        tags: ['Buyer Cart'],
        summary: 'Update a cart item quantity',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Item updated'),
            new OA\Response(response: 403, description: 'Not the owner'),
        ],
    )]
    public function update(UpdateCartItemRequest $request, CartItem $item): JsonResponse
    {
        $this->authorize('update', $item);

        return response()->json($this->carts->updateQuantity($item, $request->validated()['quantity']));
    }

    #[OA\Delete(
        path: '/api/v1/buyer/cart/items/{item}',
        tags: ['Buyer Cart'],
        summary: 'Remove a cart item',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 204, description: 'Item removed')],
    )]
    public function destroy(CartItem $item): JsonResponse
    {
        $this->authorize('delete', $item);

        $this->carts->removeItem($item);

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: '/api/v1/buyer/cart/clear',
        tags: ['Buyer Cart'],
        summary: 'Clear the cart',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 204, description: 'Cart cleared')],
    )]
    public function clear(Request $request): JsonResponse
    {
        $this->carts->clear($request->user());

        return response()->json(null, 204);
    }
}
