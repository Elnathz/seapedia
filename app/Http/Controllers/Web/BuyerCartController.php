<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuyerCartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function index(Request $request): Response
    {
        return Inertia::render('buyer/cart/Index', [
            'cart' => $this->carts->summary($request->user()),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        $this->carts->addItem(
            $request->user(),
            $product,
            $data['quantity'],
            (bool) ($data['replace'] ?? false),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item added to cart.')]);

        // Adding to cart can be triggered from the catalog/product pages as
        // well as the cart page itself — stay put rather than always
        // navigating to the cart.
        return back();
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse
    {
        $this->authorize('update', $item);

        $this->carts->updateQuantity($item, $request->validated()['quantity']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cart updated.')]);

        return to_route('buyer.cart.index');
    }

    public function destroy(CartItem $item): RedirectResponse
    {
        $this->authorize('delete', $item);

        $this->carts->removeItem($item);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item removed from cart.')]);

        return to_route('buyer.cart.index');
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->carts->clear($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cart cleared.')]);

        return to_route('buyer.cart.index');
    }
}
