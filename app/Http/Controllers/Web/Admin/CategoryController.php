<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    public function index(): Response
    {
        return Inertia::render('admin/categories/Index', [
            'categories' => $this->present($this->categories->listForAdmin()),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category created.')]);

        return to_route('admin.categories.index');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category updated.')]);

        return to_route('admin.categories.index');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if (! $this->categories->isDeletable($category)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('Category still has products or subcategories and cannot be deleted.'),
            ]);

            return back();
        }

        $this->categories->delete($category);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category deleted.')]);

        return to_route('admin.categories.index');
    }

    /**
     * @param  Collection<int, Category>  $roots
     * @return array<int, array<string, mixed>>
     */
    private function present(Collection $roots): array
    {
        return $roots->map(fn (Category $root) => [
            'id' => $root->id,
            'name' => $root->name,
            'slug' => $root->slug,
            'icon' => $root->icon,
            'parent_id' => null,
            'is_active' => $root->is_active,
            'products_count' => $root->products_count,
            'children' => $root->children->map(fn (Category $child) => [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
                'icon' => $child->icon,
                'parent_id' => $child->parent_id,
                'is_active' => $child->is_active,
                'products_count' => $child->products_count,
            ])->all(),
        ])->all();
    }
}
