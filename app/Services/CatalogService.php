<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Store;
use App\Models\User;
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
    public function index(?string $search = null, ?Category $category = null, ?string $sort = null, int $perPage = 12, ?int $seed = null): LengthAwarePaginator
    {
        $categoryIds = $category ? $this->categories->descendantIds($category) : null;

        $query = Product::query()
            ->with(['store:id,name,slug', 'category:id,name,slug'])
            ->where('is_active', true)
            ->whereHas('store', fn ($q) => $q->where('is_active', true))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($categoryIds, fn ($q) => $q->whereIn('category_id', $categoryIds));

        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'newest' => $query->latest(),
            'random' => $seed ? $query->inRandomOrder($seed) : $query->inRandomOrder(),
            default => $query->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Stores relevant to a catalog text search: active stores whose own name
     * matches, or that stock an active product matching the query. This is why
     * a search for a brand surfaces the shop itself, not only its items.
     *
     * Ranked name-matches first, then by how many matching products they carry.
     * Only public columns are selected, and `active_products_count` feeds the
     * card's "N produk" line.
     *
     * @return Collection<int, Store>
     */
    public function searchStores(string $search, int $limit = 6): Collection
    {
        $like = "%{$search}%";

        return Store::query()
            ->select(['id', 'name', 'slug', 'logo_path', 'description'])
            ->where('is_active', true)
            ->where(fn ($q) => $q
                ->where('name', 'like', $like)
                ->orWhereHas('products', fn ($pq) => $pq
                    ->where('is_active', true)
                    ->where('name', 'like', $like)))
            ->withCount([
                'products as active_products_count' => fn ($pq) => $pq->where('is_active', true),
                'products as matching_products_count' => fn ($pq) => $pq
                    ->where('is_active', true)
                    ->where('name', 'like', $like),
            ])
            // A store whose own name matches ranks above one that merely stocks a
            // matching product; the CASE is constant SQL with a bound value.
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$like])
            ->orderByDesc('matching_products_count')
            ->limit($limit)
            ->get();
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

    public function personalized(?User $user = null, int $limit = 4): Collection
    {
        $query = Product::query()
            ->with(['store:id,name,slug', 'category:id,name,slug'])
            ->where('is_active', true)
            ->whereHas('store', fn ($q) => $q->where('is_active', true));

        if ($user) {
            $topCategoryId = ProductView::where('user_id', $user->id)
                ->select('category_id')
                ->selectRaw('count(*) as views')
                ->groupBy('category_id')
                ->orderByDesc('views')
                ->value('category_id');

            if ($topCategoryId) {
                $query->where('category_id', $topCategoryId);
            } else {
                $query->inRandomOrder();
            }
        } else {
            $query->inRandomOrder();
        }

        return $query->limit($limit)->get();
    }
}
