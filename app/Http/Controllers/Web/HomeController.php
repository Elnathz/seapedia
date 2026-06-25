<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AppReviewService;
use App\Services\CatalogService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalog,
        private readonly AppReviewService $reviews,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Welcome', [
            'featured' => $this->catalog->featured(),
            'reviews' => $this->reviews->recent(),
        ]);
    }
}
