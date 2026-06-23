<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\StoreService;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __construct(private readonly StoreService $stores) {}

    /**
     * Public store page: store header + its active products (TDD §13 Day 2).
     */
    public function show(string $store): Response
    {
        $found = $this->stores->publicShow($store);

        abort_if($found === null, 404);

        return Inertia::render('stores/Show', ['store' => $found]);
    }
}
