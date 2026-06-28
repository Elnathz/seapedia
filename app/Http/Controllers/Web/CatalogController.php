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

        return Inertia::render('catalog/Index', [
            'products' => $this->catalog->index($search, $category, $sort),
            'search' => $search,
            'sort' => $sort,
            'banners' => $this->banners->forStorefront(),
            'activeCategory' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent' => $category->parent ? [
                    'name' => $category->parent->name,
                    'slug' => $category->parent->slug,
                ] : null,
            ] : null,
            'personalizedProducts' => $this->catalog->personalized($request->user()),
            'popularCategories' => $this->categories->tree()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])->take(6),
        ]);
    }

    public function show(string $product): Response
    {
        $found = $this->catalog->find($product);

        abort_if($found === null, 404);

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
