<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('q')->trim()->value();
        $categoryId = $request->integer('category') ?: null;

        $products = Product::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->with('store:id,name,slug', 'category:id,name')
            // Cluster same-category rows together (uncategorised last) so the
            // monitoring table reads per category; newest first within each.
            ->orderByRaw('category_id IS NULL')
            ->orderBy('category_id')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'filters' => ['q' => $search, 'category' => $categoryId],
        ]);
    }
}
