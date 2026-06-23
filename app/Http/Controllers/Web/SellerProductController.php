<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            return to_route('seller.store.show');
        }

        return Inertia::render('seller/products/Index', [
            'products' => $store->products()->latest()->get(),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->user()->store) {
            return to_route('seller.store.show');
        }

        return Inertia::render('seller/products/Form', [
            'product' => null,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $store = $request->user()->store;

        abort_if($store === null, 404);

        $this->products->createForStore($store, $request->validated(), $request->file('image'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product created.')]);

        return to_route('seller.products.index');
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        return Inertia::render('seller/products/Form', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->products->update($product, $request->validated(), $request->file('image'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product updated.')]);

        return to_route('seller.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product deleted.')]);

        return to_route('seller.products.index');
    }
}
