<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
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

        $variant = null;
        if (! empty($data['product_variant_id'])) {
            $variant = ProductVariant::where('product_id', $product->id)
                ->findOrFail($data['product_variant_id']);
        } elseif ($product->variants()->exists()) {
            // If the product has variants, require a variant to be selected
            return back()->withErrors(['product_variant_id' => 'Silakan pilih varian produk.']);
        }

        $this->carts->addItem(
            $request->user(),
            $product,
            $variant,
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
