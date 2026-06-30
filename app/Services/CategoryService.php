<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Active category tree for the navbar/landing surfaces: root categories
     * with their active children, each carrying a direct product count.
     * `products_total` on a root sums its own and its children's counts so
     * the UI can show a meaningful "X produk" without a second query.
     *
     * @return Collection<int, Category>
     */
    public function tree(): Collection
    {
        $roots = Category::query()
            ->active()
            ->roots()
            ->withCount('products')
            ->with(['children' => fn ($query) => $query->active()->withCount('products')])
            ->orderBy('sort_order')
            ->get();

        return $roots->each(function (Category $root): void {
            $root->setAttribute(
                'products_total',
                $root->products_count + $root->children->sum('products_count'),
            );
        });
    }

    /**
     * The category and all of its descendant ids (two levels deep), used to
     * filter the catalog so picking a parent shows products from its subtree.
     *
     * @return array<int, int>
     */
    public function descendantIds(Category $category): array
    {
        $category->loadMissing('children:id,parent_id');

        return [$category->id, ...$category->children->pluck('id')->all()];
    }

    /**
     * Resolve an active category by slug for the public catalog, eager-loading
     * the parent (breadcrumb) and children (subtree filter) it needs.
     */
    public function findActiveBySlug(string $slug): ?Category
    {
        return Category::query()
            ->active()
            ->with(['parent:id,name,slug', 'children:id,parent_id'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Flat list of all active categories (root + children combined),
     * ordered by the number of active products descending. Used for the
     * "Kategori Populer" section on the catalog homepage so child categories
     * with many products can surface alongside root categories.
     *
     * @return Collection<int, Category>
     */
    public function popularFlat(int $limit = 6): Collection
    {
        return Category::query()
            ->active()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->having('products_count', '>', 0)
            ->orderByDesc('products_count')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'parent_id']);
    }

    /**
     * Full tree (active and inactive) for the admin management table.
     *
     * @return Collection<int, Category>
     */
    public function listForAdmin(): Collection
    {
        return Category::query()
            ->roots()
            ->withCount('products')
            ->with(['children' => fn ($query) => $query->withCount('products')->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @param  array{name: string, parent_id?: ?int, icon?: ?string, is_active?: bool}  $data
     */
    public function create(array $data): Category
    {
        return Category::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'icon' => $data['icon'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => Category::query()->max('sort_order') + 1,
        ]);
    }

    /**
     * @param  array{name: string, parent_id?: ?int, icon?: ?string, is_active?: bool}  $data
     */
    public function update(Category $category, array $data): Category
    {
        $category->update([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $data['name'] === $category->name
                ? $category->slug
                : $this->uniqueSlug($data['name'], $category->id),
            'icon' => $data['icon'] ?? null,
            'is_active' => $data['is_active'] ?? $category->is_active,
        ]);

        return $category->refresh();
    }

    /**
     * A category can only be deleted once it holds no products and no
     * children, so deleting never cascades away a seller's products.
     */
    public function isDeletable(Category $category): bool
    {
        return ! $category->products()->exists() && ! $category->children()->exists();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (
            Category::query()
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
