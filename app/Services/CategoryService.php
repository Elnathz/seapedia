<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

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
        return [$category->id, ...$category->children()->pluck('id')->all()];
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
}
