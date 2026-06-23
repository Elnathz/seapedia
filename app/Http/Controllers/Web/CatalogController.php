<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    /**
     * Public catalog browse (TDD §13 Day 1; dummy data — real catalog from
     * the database arrives in Sprint 2).
     */
    public function index(Request $request): Response
    {
        $search = $request->string('q')->value() ?: null;

        return Inertia::render('catalog/Index', [
            'products' => $this->catalog->index($search),
            'search' => $search,
        ]);
    }

    public function show(string $product): Response
    {
        $found = $this->catalog->find($product);

        abort_if($found === null, 404);

        return Inertia::render('catalog/Show', ['product' => $found]);
    }
}
