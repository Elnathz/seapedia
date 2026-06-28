<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductView;
use App\Services\BannerService;
use App\Services\CatalogService;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalog,
        private readonly CategoryService $categories,
        private readonly BannerService $banners,
    ) {}

    /**
     * Public catalog browse: active products of active stores, read from the
     * database via CatalogService, with an optional `?q` name search,
     * `?category` subtree filter, and pagination.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('q')->value() ?: null;
        $categorySlug = $request->string('category')->value() ?: null;
        $sort = in_array($request->string('sort')->value(), ['price_asc', 'price_desc']) ? $request->string('sort')->value() : null;
        $category = $categorySlug ? $this->categories->findActiveBySlug($categorySlug) : null;

        $hasFilters = $search || $categorySlug || $sort || $request->has('price_min') || $request->has('price_max') || $request->has('in_stock');

        if ($hasFilters) {
            return Inertia::render('catalog/Search', [
                'products' => $this->catalog->index($search, $category, $sort),
                'filters' => $request->only(['q', 'category', 'sort', 'price_min', 'price_max', 'in_stock']),
                'categories' => $this->categories->tree()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug]),
            ]);
        }

        return Inertia::render('catalog/Index', [
            'products' => $this->catalog->index(null, null, null),
            'banners' => $this->banners->forStorefront(),
            'personalizedProducts' => $this->catalog->personalized($request->user()),
            'popularCategories' => $this->categories->tree()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])->take(6),
        ]);
    }

    public function show(string $product): Response
    {
        $found = $this->catalog->find($product);

        abort_if($found === null, 404);
        
        $found->load(['variants', 'images', 'category', 'store']);

        if (auth()->check()) {
            ProductView::create([
                'user_id' => auth()->id(),
                'product_id' => $found->id,
                'category_id' => $found->category_id,
            ]);
        }

        return Inertia::render('catalog/Show', ['product' => $found]);
    }
}
