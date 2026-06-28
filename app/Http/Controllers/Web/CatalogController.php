<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
        $category = $categorySlug ? $this->categories->findActiveBySlug($categorySlug) : null;

        return Inertia::render('catalog/Index', [
            'products' => $this->catalog->index($search, $category),
            'search' => $search,
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
        ]);
    }

    public function show(string $product): Response
    {
        $found = $this->catalog->find($product);

        abort_if($found === null, 404);

        return Inertia::render('catalog/Show', ['product' => $found]);
    }
}
