<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('q')->trim()->value();

        $products = Product::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->with('store:id,name,slug', 'category:id,name')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'filters' => ['q' => $search],
        ]);
    }
}
