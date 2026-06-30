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
        $sort = in_array($request->string('sort')->value(), ['price_asc', 'price_desc', 'newest', 'random']) ? $request->string('sort')->value() : null;
        $category = $categorySlug ? $this->categories->findActiveBySlug($categorySlug) : null;

        // "random" is the default for Index, so if only random is present, we don't treat it as a filter
        $isSortFilter = $sort && $sort !== 'random';
        $hasFilters = $search || $categorySlug || $isSortFilter || $request->has('price_min') || $request->has('price_max') || $request->has('in_stock');

        if (!$request->has('page') || $request->integer('page') === 1) {
            $seed = random_int(1, 999999);
            session()->put('catalog_seed', $seed);
        } else {
            $seed = session('catalog_seed', random_int(1, 999999));
        }

        if ($hasFilters) {
            $products = $this->catalog->index($search, $category, $sort, 12, $seed);
            
            // Get relevant category IDs if there's a search query and products exist
            $relevantCategoryIds = collect();
            if ($search && $products->total() > 0) {
                $relevantCategoryIds = \App\Models\Product::query()
                    ->where('is_active', true)
                    ->where('name', 'like', "%{$search}%")
                    ->distinct()
                    ->pluck('category_id');
            }

            $categoriesTree = $this->categories->tree()->map(function ($c) use ($relevantCategoryIds) {
                $children = $c->children->map(fn ($child) => ['id' => $child->id, 'name' => $child->name, 'slug' => $child->slug]);
                
                // If we are filtering by relevant categories, only include children that are relevant
                if ($relevantCategoryIds->isNotEmpty()) {
                    $children = $children->filter(fn ($child) => $relevantCategoryIds->contains($child['id']))->values();
                }

                return [
                    'id' => $c->id, 
                    'name' => $c->name, 
                    'slug' => $c->slug,
                    'children' => $children,
                ];
            });

            // If filtering, remove parents that have no relevant children
            if ($relevantCategoryIds->isNotEmpty()) {
                $categoriesTree = $categoriesTree->filter(fn ($c) => $c['children']->count() > 0)->values();
            }

            return Inertia::render('catalog/Search', [
                'products'       => $products,
                'filters'        => $request->only(['q', 'category', 'sort', 'price_min', 'price_max', 'in_stock']),
                'categories'     => $categoriesTree,
                'activeCategory' => $category,
            ]);
        }

        if (!$request->has('page') || $request->integer('page') === 1) {
            $seed = random_int(1, 999999);
            session()->put('catalog_seed', $seed);
        } else {
            $seed = session('catalog_seed', random_int(1, 999999));
        }

        return Inertia::render('catalog/Index', [
            'products' => $this->catalog->index(null, null, $sort ?: 'random', 12, $seed),
            'banners' => $this->banners->forStorefront(),
            'personalizedProducts' => $this->catalog->personalized($request->user()),
            'popularCategories' => $this->categories->popularFlat(6),
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
        
        $relatedProducts = \App\Models\Product::query()
            ->with(['store', 'category'])
            ->where('is_active', true)
            ->whereHas('store', fn($q) => $q->where('is_active', true))
            ->where('category_id', $found->category_id)
            ->where('id', '!=', $found->id)
            ->inRandomOrder()
            ->take(6)
            ->get();

        return Inertia::render('catalog/Show', [
            'product' => $found,
            'relatedProducts' => $relatedProducts
        ]);
    }
}
