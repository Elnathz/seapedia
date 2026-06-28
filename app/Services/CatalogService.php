<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductView;
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
    public function index(?string $search = null, ?Category $category = null, ?string $sort = null, int $perPage = 12): LengthAwarePaginator
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
            default => $query->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
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
