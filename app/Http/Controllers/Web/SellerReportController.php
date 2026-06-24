<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            return to_route('seller.store.show');
        }

        return Inertia::render('seller/reports/Index', [
            'report' => $this->reports->sellerIncome($store),
        ]);
    }
}
