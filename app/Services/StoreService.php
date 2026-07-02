<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Str;

class StoreService
{
    /**
     * @param  array{name: string, description?: ?string, full_address?: ?string, province?: ?string, city?: ?string, district?: ?string, village?: ?string, postal_code?: ?string, origin_latitude?: ?float, origin_longitude?: ?float}  $data
     */
    public function createForUser(User $user, array $data): Store
    {
        return Store::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'full_address' => $data['full_address'] ?? null,
            'province' => $data['province'] ?? null,
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'village' => $data['village'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'origin_latitude' => $data['origin_latitude'] ?? null,
            'origin_longitude' => $data['origin_longitude'] ?? null,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array{name: string, description?: ?string, full_address?: ?string, province?: ?string, city?: ?string, district?: ?string, village?: ?string, postal_code?: ?string, origin_latitude?: ?float, origin_longitude?: ?float}  $data
     */
    public function update(Store $store, array $data): Store
    {
        $store->update([
            'name' => $data['name'],
            'slug' => $data['name'] === $store->name
                ? $store->slug
                : $this->uniqueSlug($data['name'], $store->id),
            'description' => $data['description'] ?? null,
            'full_address' => $data['full_address'] ?? $store->full_address,
            'province' => $data['province'] ?? $store->province,
            'city' => $data['city'] ?? $store->city,
            'district' => $data['district'] ?? $store->district,
            'village' => $data['village'] ?? $store->village,
            'postal_code' => $data['postal_code'] ?? $store->postal_code,
            'origin_latitude' => $data['origin_latitude'] ?? $store->origin_latitude,
            'origin_longitude' => $data['origin_longitude'] ?? $store->origin_longitude,
        ]);

        return $store->refresh();
    }

    /**
     * Public store page: only an active store, with only its active
     * products eager-loaded (no N+1).
     */
    public function publicShow(string $slug): ?Store
    {
        return Store::query()
            ->select(['id', 'name', 'slug', 'description', 'is_active', 'created_at'])
            ->withCount('products as products_count')
            ->with(['products' => fn ($query) => $query
                ->select(['id', 'store_id', 'name', 'slug', 'price', 'image_path'])
                ->where('is_active', true)
                ->latest()])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (
            Store::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
