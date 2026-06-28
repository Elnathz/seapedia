<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminStoreController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('q')->trim()->value();

        $stores = Store::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->with('owner:id,name,email')
            ->withCount('products')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/stores/Index', [
            'stores' => $stores,
            'filters' => ['q' => $search],
        ]);
    }
}
