<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogService
{
    /**
     * Public catalog: only active products belonging to active stores
     * (eager-loaded, no N+1), optionally filtered by name.
     */
    public function index(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->with('store')
            ->where('is_active', true)
            ->whereHas('store', fn ($query) => $query->where('is_active', true))
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(string $slug): ?Product
    {
        return Product::query()
            ->with('store')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('store', fn ($query) => $query->where('is_active', true))
            ->first();
    }
}
