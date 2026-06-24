<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerOrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    /**
     * Incoming orders for the seller's store — read-only this sprint; the
     * "process" action (Sedang Dikemas → Menunggu Pengirim) is Sprint 4.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            return to_route('seller.store.show');
        }

        return Inertia::render('seller/orders/Index', [
            'orders' => $this->orders->forSeller($store),
        ]);
    }
}
