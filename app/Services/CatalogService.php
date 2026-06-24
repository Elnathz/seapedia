<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogService
{
    /**
     * Public catalog: only active products belonging to active stores
     * (eager-loaded, no N+1), optionally filtered by name. The store is
     * loaded with only its public columns so the payload never exposes
     * internal fields (owner `user_id`, timestamps) to the client.
     */
    public function index(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->with('store:id,name,slug')
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
            ->with('store:id,name,slug,is_active')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('store', fn ($query) => $query->where('is_active', true))
            ->first();
    }
}
