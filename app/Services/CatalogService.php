<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogService
{
    public function __construct(private readonly CategoryService $categories) {}

    /**
     * Public catalog: only active products belonging to active stores
     * (eager-loaded, no N+1), optionally filtered by name and/or category.
     * Filtering by a parent category includes its whole subtree. The store is
     * loaded with only its public columns so the payload never exposes
     * internal fields (owner `user_id`, timestamps) to the client.
     */
    public function index(?string $search = null, ?Category $category = null, int $perPage = 12): LengthAwarePaginator
    {
        $categoryIds = $category ? $this->categories->descendantIds($category) : null;

        return Product::query()
            ->with(['store:id,name,slug', 'category:id,name,slug'])
            ->where('is_active', true)
            ->whereHas('store', fn ($query) => $query->where('is_active', true))
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($categoryIds, fn ($query) => $query->whereIn('category_id', $categoryIds))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function featured(int $limit = 6): Collection
    {
        return Product::query()
            ->with('store:id,name,slug')
            ->where('is_active', true)
            ->whereHas('store', fn ($query) => $query->where('is_active', true))
            ->latest()
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'price', 'image_path', 'store_id']);
    }

    public function find(string $slug): ?Product
    {
        return Product::query()
            ->with(['store:id,name,slug,is_active', 'category:id,name,slug,parent_id'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('store', fn ($query) => $query->where('is_active', true))
            ->first();
    }
}
