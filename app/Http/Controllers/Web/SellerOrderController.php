<?php

namespace App\Http\Controllers\Web;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerOrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    /**
     * Incoming orders for the seller's store, newest first.
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

    public function show(int $order): Response
    {
        $fullOrder = $this->orders->findForSeller($order);

        abort_if($fullOrder === null, 404);

        $this->authorize('view', $fullOrder);

        return Inertia::render('seller/orders/Show', ['order' => $fullOrder]);
    }

    /**
     * The only seller-initiated transition this sprint (§5.6): Sedang
     * Dikemas → Menunggu Pengirim. Ownership enforced by OrderPolicy;
     * the transition table itself lives in OrderService.
     */
    public function process(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('process', $order);

        $this->orders->transition(
            $order,
            OrderStatus::MenungguPengirim,
            $request->user()->id,
            'Diproses oleh penjual',
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Order processed.')]);

        return to_route('seller.orders.show', $order);
    }
}
