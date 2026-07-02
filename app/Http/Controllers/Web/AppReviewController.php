<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppReviewRequest;
use App\Services\AppReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppReviewController extends Controller
{
    public function __construct(private readonly AppReviewService $reviews) {}

    /**
     * Public, paginated review list (TDD §8 "GET /reviews").
     */
    public function index(Request $request): Response
    {
        return Inertia::render('reviews/Index', [
            'reviews' => $this->reviews->paginated(
                12,
                $request->query('role'),
                $request->query('rating') ? (int) $request->query('rating') : null
            ),
            'filters' => $request->only(['role', 'rating']),
        ]);
    }

    /**
     * Guest-allowed review submission (TDD §8 "POST /reviews").
     */
    public function store(StoreAppReviewRequest $request): RedirectResponse
    {
        $this->reviews->submit($request->validated(), $request->user()?->id);

        return redirect()->back()->with('success', 'Ulasan berhasil dikirim, terima kasih!');
    }
}
